<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $reportHeading }}</title>
    <style>
        body {
            margin: 0;
            padding: 18mm 12mm 12mm;
            font-family: Arial, sans-serif;
            color: #000;
            background: #fff;
        }
        .report-sheet-header {
            text-align: center;
            margin-bottom: 18px;
        }
        .report-sheet-title {
            margin: 0;
            font-size: 18px;
            font-weight: 400;
            line-height: 1.2;
        }
        .report-sheet-subtitle {
            margin: 4px 0 0;
            font-size: 12px;
            line-height: 1.2;
        }
        .report-sheet-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .report-sheet-table th,
        .report-sheet-table td {
            border: 1px solid #000;
            padding: 4px 5px;
            font-size: 11px;
            font-weight: 400;
            line-height: 1.25;
            text-align: left;
            vertical-align: top;
            word-wrap: break-word;
        }
        .report-sheet-table th {
            text-transform: uppercase;
        }
        @page {
            size: landscape;
            margin: 12mm;
        }
    </style>
</head>
<body onload="window.print()">
    <div class="report-sheet-header">
        <h1 class="report-sheet-title">{{ $reportHeading }}</h1>
        <div class="report-sheet-subtitle">{{ $reportSubheading }}</div>
    </div>

    <table class="report-sheet-table">
        <colgroup>
            <col style="width:88px;">
            <col style="width:190px;">
            <col style="width:150px;">
            <col style="width:190px;">
            <col style="width:170px;">
            <col style="width:190px;">
            <col style="width:190px;">
            <col style="width:110px;">
            <col style="width:145px;">
        </colgroup>
        <thead>
            <tr>
                <th>Permit ID</th>
                <th>Owner</th>
                <th>Building Type</th>
                <th>Building Category</th>
                <th>Barangay</th>
                <th>City/Municipality</th>
                <th>Province</th>
                <th>Status</th>
                <th>Created</th>
            </tr>
        </thead>
        <tbody>
            @forelse($records as $record)
                <tr>
                    <td>{{ $record->permit_id }}</td>
                    <td>{{ $record->owner_full_name }}</td>
                    <td>{{ $record->buildingType?->name }}</td>
                    <td>{{ $record->buildingCategory?->name }}</td>
                    <td>{{ $record->barangay }}</td>
                    <td>{{ $record->city_municipality }}</td>
                    <td>{{ $record->province }}</td>
                    <td>{{ $record->status }}</td>
                    <td>{{ $record->created_at?->format('M d, Y') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9">No records matched the selected report filters.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
