<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Date</th>
            <th>Vehicle</th>
            <th>Performed By</th>
            <th>Odometer</th>
            <th>Description</th>
            <th>Labor Cost</th>
            <th>Materials Cost</th>
            <th>Total Cost</th>
            <th>Next Due Date</th>
            <th>Next Due Odometer</th>
            <th>Materials Used</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($records as $record)
            <tr>
                <td>{{ $record->id }}</td>
                <td>{{ $record->performed_at?->format('Y-m-d H:i') }}</td>
                <td>{{ $record->vehicle?->plate_number ?? '' }}</td>
                <td>{{ $record->performedBy?->name ?? '' }}</td>
                <td>{{ $record->odometer_reading }}</td>
                <td>{{ $record->description_of_work }}</td>
                <td>{{ $record->personnel_labor_cost }}</td>
                <td>{{ $record->materials_cost_total }}</td>
                <td>{{ $record->total_cost }}</td>
                <td>{{ $record->next_maintenance_due_at?->format('Y-m-d') }}</td>
                <td>{{ $record->next_maintenance_due_odometer }}</td>
                <td>{{ $record->materials->map(fn($m) => $m->name . ' (' . $m->quantity . ' ' . $m->unit . ')')->implode('; ') }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
