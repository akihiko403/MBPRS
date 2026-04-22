<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class BackupRestoreController extends Controller
{
    public function index(): View|RedirectResponse
    {
        if ($redirect = $this->redirectIfCannotAccess('backup-restore')) {
            return $redirect;
        }

        return view('backup-restore.index', [
            'title' => 'Backup & Restore',
            'subtitle' => 'Download and restore database backups.',
            'databaseName' => config('database.connections.'.config('database.default').'.database'),
            'connectionName' => config('database.default'),
        ]);
    }

    public function backup(): StreamedResponse|RedirectResponse
    {
        if ($redirect = $this->redirectIfCannotAccess('backup-restore')) {
            return $redirect;
        }

        $databaseName = config('database.connections.'.config('database.default').'.database');
        $fileName = 'mbprs-backup-'.now()->format('Y-m-d-His').'.sql';

        if ($this->defaultDriver() === 'pgsql') {
            try {
                $output = $this->runPostgresDump();
            } catch (Throwable $exception) {
                report($exception);

                return back()->with('error', 'Database backup failed. Please make sure PostgreSQL client tools are available and try again.');
            }

            return response(
                "-- MBPRS database backup\n"
                ."-- Database: {$databaseName}\n"
                ."-- Generated: ".now()->toDateTimeString()."\n\n"
                .$output,
                200,
                [
                    'Content-Type' => 'application/sql',
                    'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
                ],
            );
        }

        return response()->streamDownload(function () use ($databaseName): void {
            echo "-- MBPRS database backup\n";
            echo "-- Database: {$databaseName}\n";
            echo "-- Generated: ".now()->toDateTimeString()."\n\n";
            echo "SET FOREIGN_KEY_CHECKS=0;\n\n";

            foreach ($this->tables() as $table) {
                $create = DB::selectOne("SHOW CREATE TABLE `{$table}`");
                $createSql = $create->{'Create Table'};

                echo "DROP TABLE IF EXISTS `{$table}`;\n";
                echo $createSql.";\n\n";

                DB::table($table)->orderByRaw('1')->chunk(200, function ($rows) use ($table): void {
                    foreach ($rows as $row) {
                        $values = collect((array) $row)
                            ->map(fn ($value) => $this->sqlValue($value))
                            ->implode(', ');

                        echo "INSERT INTO `{$table}` (`".implode('`, `', array_keys((array) $row))."`) VALUES ({$values});\n";
                    }
                });

                echo "\n";
            }

            echo "SET FOREIGN_KEY_CHECKS=1;\n";
        }, $fileName, [
            'Content-Type' => 'application/sql',
        ]);
    }

    public function restore(Request $request): RedirectResponse
    {
        if ($redirect = $this->redirectIfCannotAccess('backup-restore')) {
            return $redirect;
        }

        $validated = $request->validate([
            'backup_file' => ['required', 'file', 'extensions:sql,txt', 'max:51200'],
        ]);

        $sql = file_get_contents($validated['backup_file']->getRealPath());

        if (
            ! str_contains($sql, 'MBPRS database backup')
            && ! str_contains($sql, 'PostgreSQL database dump')
            && ! str_contains($sql, 'pg_dump')
        ) {
            return back()->with('error', 'Only MBPRS backup files can be restored.');
        }

        if ($this->defaultDriver() === 'pgsql') {
            try {
                $this->runPostgresRestore($validated['backup_file']->getRealPath());
            } catch (Throwable $exception) {
                report($exception);

                return back()->with('error', 'Database restore failed. Please make sure the backup file is valid and try again.');
            }

            return redirect()->route('backup-restore.index')->with('success', 'Database restored successfully.');
        }

        $sql = preg_replace('/^\s*--.*$/m', '', $sql);

        try {
            foreach ($this->splitStatements($sql) as $statement) {
                DB::unprepared($statement);
            }
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('error', 'Database restore failed. Please make sure the backup file is valid and try again.');
        }

        return redirect()->route('backup-restore.index')->with('success', 'Database restored successfully.');
    }

    private function defaultDriver(): string
    {
        return (string) config('database.default');
    }

    private function postgresConnectionConfig(): array
    {
        return config('database.connections.'.$this->defaultDriver(), []);
    }

    private function runPostgresDump(): string
    {
        $config = $this->postgresConnectionConfig();

        $command = sprintf(
            'pg_dump --clean --if-exists --no-owner --no-privileges --encoding=UTF8 --host=%s --port=%s --username=%s --dbname=%s',
            escapeshellarg((string) ($config['host'] ?? '127.0.0.1')),
            escapeshellarg((string) ($config['port'] ?? '5432')),
            escapeshellarg((string) ($config['username'] ?? '')),
            escapeshellarg((string) ($config['database'] ?? ''))
        );

        return $this->runShellCommand($command, [
            'PGPASSWORD' => (string) ($config['password'] ?? ''),
        ]);
    }

    private function runPostgresRestore(string $filePath): void
    {
        $config = $this->postgresConnectionConfig();

        $command = sprintf(
            'psql --set ON_ERROR_STOP=on --host=%s --port=%s --username=%s --dbname=%s --file=%s',
            escapeshellarg((string) ($config['host'] ?? '127.0.0.1')),
            escapeshellarg((string) ($config['port'] ?? '5432')),
            escapeshellarg((string) ($config['username'] ?? '')),
            escapeshellarg((string) ($config['database'] ?? '')),
            escapeshellarg($filePath)
        );

        $this->runShellCommand($command, [
            'PGPASSWORD' => (string) ($config['password'] ?? ''),
        ]);
    }

    private function runShellCommand(string $command, array $environment = []): string
    {
        $descriptorSpec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = proc_open($command, $descriptorSpec, $pipes, base_path(), array_merge($_ENV, $_SERVER, $environment));

        if (! is_resource($process)) {
            throw new RuntimeException('Unable to start database process.');
        }

        fclose($pipes[0]);

        $output = stream_get_contents($pipes[1]);
        $errorOutput = stream_get_contents($pipes[2]);

        fclose($pipes[1]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);

        if ($exitCode !== 0) {
            throw new RuntimeException(trim($errorOutput) !== '' ? trim($errorOutput) : 'Database process failed.');
        }

        return $output;
    }

    private function tables(): array
    {
        return collect(DB::select('SHOW TABLES'))
            ->map(fn ($table) => array_values((array) $table)[0])
            ->filter(fn ($table) => Schema::hasTable($table))
            ->values()
            ->all();
    }

    private function sqlValue(mixed $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        return DB::getPdo()->quote((string) $value);
    }

    private function splitStatements(string $sql): array
    {
        $statements = [];
        $current = '';
        $quote = null;
        $escaped = false;

        foreach (str_split($sql) as $char) {
            $current .= $char;

            if ($escaped) {
                $escaped = false;
                continue;
            }

            if ($char === '\\') {
                $escaped = true;
                continue;
            }

            if (($char === "'" || $char === '"') && ($quote === null || $quote === $char)) {
                $quote = $quote === $char ? null : $char;
                continue;
            }

            if ($char === ';' && $quote === null) {
                $statement = trim($current);
                $current = '';

                if ($statement !== '' && ! str_starts_with($statement, '--')) {
                    $statements[] = $statement;
                }
            }
        }

        $lastStatement = trim($current);

        if ($lastStatement !== '') {
            $statements[] = $lastStatement;
        }

        return $statements;
    }
}
