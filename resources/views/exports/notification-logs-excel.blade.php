<table>
    <thead>
        <tr>
            <th>Created At</th>
            <th>Vehicle</th>
            <th>Channel</th>
            <th>Type</th>
            <th>Severity</th>
            <th>Trigger</th>
            <th>Due Date</th>
            <th>Due Odometer</th>
            <th>Status</th>
            <th>Retry Count</th>
            <th>Error</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($logs as $log)
            @php
                $meta = $log->meta ?? [];
            @endphp
            <tr>
                <td>{{ $log->created_at?->format('Y-m-d H:i') }}</td>
                <td>{{ $log->vehicle?->plate_number ?? '' }}</td>
                <td>{{ $log->channel }}</td>
                <td>{{ $log->type }}</td>
                <td>{{ $log->type_label }}</td>
                <td>{{ $log->trigger_label }}</td>
                <td>{{ $meta['next_maintenance_due_at'] ?? '' }}</td>
                <td>{{ $meta['next_maintenance_due_odometer'] ?? '' }}</td>
                <td>{{ $log->status }}</td>
                <td>{{ $log->retry_count }}</td>
                <td>{{ $log->error_message }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
