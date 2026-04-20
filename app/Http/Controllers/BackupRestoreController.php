<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BackupRestoreController extends Controller
{
    public function index(): View|RedirectResponse
    {
        if ($redirect = $this->redirectIfMissingRole(Role::ADMINISTRATOR)) {
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
        if ($redirect = $this->redirectIfMissingRole(Role::ADMINISTRATOR)) {
            return $redirect;
        }

        $databaseName = config('database.connections.'.config('database.default').'.database');
        $fileName = 'mbprs-backup-'.now()->format('Y-m-d-His').'.sql';

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
        if ($redirect = $this->redirectIfMissingRole(Role::ADMINISTRATOR)) {
            return $redirect;
        }

        $validated = $request->validate([
            'backup_file' => ['required', 'file', 'mimes:sql,txt', 'max:51200'],
        ]);

        $sql = file_get_contents($validated['backup_file']->getRealPath());

        if (! str_contains($sql, 'MBPRS database backup')) {
            return back()->with('error', 'Only MBPRS backup files can be restored.');
        }

        $sql = preg_replace('/^\s*--.*$/m', '', $sql);

        DB::transaction(function () use ($sql): void {
            foreach ($this->splitStatements($sql) as $statement) {
                DB::unprepared($statement);
            }
        });

        return redirect()->route('backup-restore.index')->with('success', 'Database restored successfully.');
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
