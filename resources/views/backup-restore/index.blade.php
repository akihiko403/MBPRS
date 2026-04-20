@extends('layouts.app')

@section('content')
<div class="card">
    <h3 style="margin:0 0 6px;">Database Backup & Restore</h3>
    <div class="muted" style="margin-bottom:20px;">Download a full database backup or restore from an MBPRS backup file.</div>

    <div class="table-wrap">
        <table class="backup-table">
            <thead>
                <tr>
                    <th>Action</th>
                    <th>Description</th>
                    <th>Database</th>
                    <th>Controls</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Back Up Database</strong></td>
                    <td class="muted">Create a downloadable SQL copy of the current database.</td>
                    <td>{{ $connectionName }} / {{ $databaseName }}</td>
                    <td>
                        <form method="POST" action="{{ route('backup-restore.backup') }}">
                            @csrf
                            <button class="btn" type="submit">Download Backup</button>
                        </form>
                    </td>
                </tr>
                <tr>
                    <td><strong>Restore Database</strong></td>
                    <td class="muted">Upload an MBPRS `.sql` backup file to replace current database content.</td>
                    <td>{{ $connectionName }} / {{ $databaseName }}</td>
                    <td>
                        <form class="backup-restore-form" method="POST" action="{{ route('backup-restore.restore') }}" enctype="multipart/form-data" data-confirm-save data-save-message="Restore this database backup? Current records may be replaced.">
                            @csrf
                            <input type="file" name="backup_file" accept=".sql,.txt" required>
                            <button class="btn danger" type="submit">Restore Backup</button>
                        </form>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<style>
    .backup-table td:last-child, .backup-table th:last-child { text-align:right; }
    .backup-table form { display:inline-flex; justify-content:flex-end; width:auto; margin:0; }
    .backup-table .btn { width:auto; white-space:nowrap; }
    .backup-restore-form { align-items:center; gap:8px; }
    .backup-restore-form input[type="file"] { max-width:260px; min-height:42px; padding:8px 10px; }
    @media (max-width:760px) {
        .backup-table td:last-child, .backup-table th:last-child { text-align:left; }
        .backup-table form, .backup-restore-form { width:100%; flex-direction:column; align-items:stretch; }
        .backup-restore-form input[type="file"], .backup-table .btn { max-width:none; width:100%; }
    }
</style>
@endsection
