<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Vehicle</th>
            <th>Driver</th>
            <th>Destination</th>
            <th>Purpose</th>
            <th>Departure</th>
            <th>Return</th>
            <th>Odometer Start</th>
            <th>Odometer End</th>
            <th>Distance</th>
            <th>Status</th>
            <th>Requested By</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($tickets as $ticket)
            @php
                $distance = ($ticket->odometer_end && $ticket->odometer_start)
                    ? $ticket->odometer_end - $ticket->odometer_start
                    : null;
            @endphp
            <tr>
                <td>{{ $ticket->id }}</td>
                <td>{{ $ticket->vehicle?->plate_number ?? '' }}</td>
                <td>{{ $ticket->driver_name }}</td>
                <td>{{ $ticket->destination }}</td>
                <td>{{ $ticket->purpose }}</td>
                <td>{{ $ticket->departure_at?->format('Y-m-d H:i') }}</td>
                <td>{{ $ticket->return_at?->format('Y-m-d H:i') }}</td>
                <td>{{ $ticket->odometer_start }}</td>
                <td>{{ $ticket->odometer_end }}</td>
                <td>{{ $distance }}</td>
                <td>{{ ucfirst($ticket->status) }}</td>
                <td>{{ $ticket->requestedBy?->name ?? '' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
