<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Plate Number</th>
            <th>Vehicle Type</th>
            <th>Make</th>
            <th>Model</th>
            <th>Year</th>
            <th>Chassis Number</th>
            <th>Engine Number</th>
            <th>Driver / Operator</th>
            <th>Contact Number</th>
            <th>Status</th>
            <th>Current Odometer</th>
            <th>Next Maintenance Due</th>
            <th>Next Maintenance Odometer</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($vehicles as $vehicle)
            <tr>
                <td>{{ $vehicle->id }}</td>
                <td>{{ $vehicle->plate_number }}</td>
                <td>{{ $vehicle->vehicle_type }}</td>
                <td>{{ $vehicle->make }}</td>
                <td>{{ $vehicle->model }}</td>
                <td>{{ $vehicle->year }}</td>
                <td>{{ $vehicle->chassis_number }}</td>
                <td>{{ $vehicle->engine_number }}</td>
                <td>{{ $vehicle->driver_operator }}</td>
                <td>{{ $vehicle->contact_number }}</td>
                <td>{{ $vehicle->status }}</td>
                <td>{{ $vehicle->current_odometer }}</td>
                <td>{{ $vehicle->next_maintenance_due_at?->format('Y-m-d') }}</td>
                <td>{{ $vehicle->next_maintenance_due_odometer }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
