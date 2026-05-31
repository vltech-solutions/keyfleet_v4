<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Customer Export</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #333; padding: 6px; text-align: left; }
        th { background-color: #f0f0f0; }
        h2 { text-align: center; }
    </style>
</head>
<body>
    <h2>{{ $exportData['company']->name ?? 'Customer List' }}</h2>

    <table>
        <thead>
            <tr>
                <th>Customer</th>
                <th>Address</th>
                <th>Contact Number</th>
                <th>Bookings Count</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($exportData['customers'] as $customer)
                <tr>
                    <td>{{ $customer->customer_name }}</td>
                    <td>{{ $customer->address }}</td>
                    <td>{{ $customer->contact_number }}</td>
                    <td>{{ $customer->bookings_count }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
