<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trip Tickets Report</title>
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
        .status-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
        }
        .status-approved {
            background-color: #dbeafe;
            color: #1e40af;
        }
        .status-ongoing {
            background-color: #e0e7ff;
            color: #3730a3;
        }
        .status-completed {
            background-color: #d1fae5;
            color: #065f46;
        }
        .status-cancelled {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 9px;
            color: #666;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ config('app.name', 'Motorpool System') }}</h1>
        <p>Trip Tickets Report</p>
        <p>Generated on {{ now()->format('F d, Y h:i A') }}</p>
    </div>

    <div class="summary">
        <span class="summary-row">
            <span class="summary-label">Total Tickets:</span>
            <span class="summary-value">{{ $tickets->count() }}</span>
        </span>
        <span class="summary-row">
            <span class="summary-label">Completed:</span>
            <span class="summary-value">{{ $tickets->where('status', 'completed')->count() }}</span>
        </span>
        <span class="summary-row">
            <span class="summary-label">Pending:</span>
            <span class="summary-value">{{ $tickets->where('status', 'pending')->count() }}</span>
        </span>
        <span class="summary-row">
            <span class="summary-label">Ongoing:</span>
            <span class="summary-value">{{ $tickets->where('status', 'ongoing')->count() }}</span>
        </span>
        @php
            $totalDistance = $tickets->sum(function ($ticket) {
                return ($ticket->odometer_end && $ticket->odometer_start)
                    ? $ticket->odometer_end - $ticket->odometer_start
                    : 0;
            });
        @endphp
        <span class="summary-row">
            <span class="summary-label">Total Distance:</span>
            <span class="summary-value">{{ number_format($totalDistance) }} km</span>
        </span>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 10%">Date</th>
                <th style="width: 10%">Vehicle</th>
                <th style="width: 12%">Driver</th>
                <th style="width: 15%">Destination</th>
                <th style="width: 15%">Purpose</th>
                <th style="width: 8%" class="text-right">Start</th>
                <th style="width: 8%" class="text-right">End</th>
                <th style="width: 8%" class="text-right">Distance</th>
                <th style="width: 10%">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($tickets as $ticket)
                @php
                    $distance = ($ticket->odometer_end && $ticket->odometer_start)
                        ? $ticket->odometer_end - $ticket->odometer_start
                        : null;
                @endphp
                <tr>
                    <td>{{ $ticket->departure_at?->format('M d, Y') }}</td>
                    <td>{{ $ticket->vehicle?->plate_number ?? '—' }}</td>
                    <td>{{ $ticket->driver_name }}</td>
                    <td>{{ Str::limit($ticket->destination, 25) }}</td>
                    <td>{{ Str::limit($ticket->purpose, 25) }}</td>
                    <td class="text-right">{{ $ticket->odometer_start ? number_format($ticket->odometer_start) : '—' }}</td>
                    <td class="text-right">{{ $ticket->odometer_end ? number_format($ticket->odometer_end) : '—' }}</td>
                    <td class="text-right">{{ $distance ? number_format($distance) . ' km' : '—' }}</td>
                    <td>
                        <span class="status-badge status-{{ $ticket->status }}">
                            {{ ucfirst($ticket->status) }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center">No trip tickets found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>{{ config('app.name', 'Motorpool System') }} &copy; {{ date('Y') }}</p>
    </div>
</body>
</html>
