<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Records Report</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #4f46e5;
            padding-bottom: 10px;
        }
        .header h1 {
            font-size: 18px;
            color: #4f46e5;
            margin-bottom: 5px;
        }
        .header p {
            font-size: 10px;
            color: #666;
        }
        .summary {
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f3f4f6;
            border-radius: 4px;
        }
        .summary-row {
            display: inline-block;
            margin-right: 20px;
        }
        .summary-label {
            font-weight: bold;
            color: #666;
        }
        .summary-value {
            color: #111;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th {
            background-color: #4f46e5;
            color: white;
            padding: 8px 6px;
            text-align: left;
            font-size: 9px;
            text-transform: uppercase;
        }
        td {
            padding: 6px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top;
        }
        tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .total-row {
            background-color: #e5e7eb !important;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 9px;
            color: #666;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ config('app.name', 'Motorpool System') }}</h1>
        <p>Maintenance Records Report</p>
        <p>Generated on {{ now()->format('F d, Y h:i A') }}</p>
    </div>

    <div class="summary">
        <span class="summary-row">
            <span class="summary-label">Total Records:</span>
            <span class="summary-value">{{ $records->count() }}</span>
        </span>
        <span class="summary-row">
            <span class="summary-label">Total Labor Cost:</span>
            <span class="summary-value">₱{{ number_format($records->sum('personnel_labor_cost'), 2) }}</span>
        </span>
        <span class="summary-row">
            <span class="summary-label">Total Materials Cost:</span>
            <span class="summary-value">₱{{ number_format($records->sum('materials_cost_total'), 2) }}</span>
        </span>
        <span class="summary-row">
            <span class="summary-label">Grand Total:</span>
            <span class="summary-value">₱{{ number_format($records->sum('total_cost'), 2) }}</span>
        </span>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 12%">Date</th>
                <th style="width: 12%">Vehicle</th>
                <th style="width: 10%">Odometer</th>
                <th style="width: 26%">Description</th>
                <th style="width: 10%" class="text-right">Labor</th>
                <th style="width: 10%" class="text-right">Materials</th>
                <th style="width: 10%" class="text-right">Total</th>
                <th style="width: 10%">Next Due</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($records as $record)
                <tr>
                    <td>{{ $record->performed_at?->format('M d, Y') }}</td>
                    <td>{{ $record->vehicle?->plate_number ?? '—' }}</td>
                    <td class="text-right">{{ $record->odometer_reading ? number_format($record->odometer_reading) : '—' }}</td>
                    <td>{{ Str::limit($record->description_of_work, 50) }}</td>
                    <td class="text-right">₱{{ number_format($record->personnel_labor_cost, 2) }}</td>
                    <td class="text-right">₱{{ number_format($record->materials_cost_total, 2) }}</td>
                    <td class="text-right">₱{{ number_format($record->total_cost, 2) }}</td>
                    <td>{{ $record->next_maintenance_due_at?->format('M d, Y') ?? '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">No maintenance records found.</td>
                </tr>
            @endforelse
            @if ($records->isNotEmpty())
                <tr class="total-row">
                    <td colspan="4" class="text-right">Totals:</td>
                    <td class="text-right">₱{{ number_format($records->sum('personnel_labor_cost'), 2) }}</td>
                    <td class="text-right">₱{{ number_format($records->sum('materials_cost_total'), 2) }}</td>
                    <td class="text-right">₱{{ number_format($records->sum('total_cost'), 2) }}</td>
                    <td></td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="footer">
        <p>{{ config('app.name', 'Motorpool System') }} &copy; {{ date('Y') }}</p>
    </div>
</body>
</html>
