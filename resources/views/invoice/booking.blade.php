<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $booking->payment->order_id }}</title>
    <style>
        body { font-family: sans-serif; font-size: 14px; color:#333; }
        h2, h3 { text-align: center; margin: 0; padding: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #000; padding: 8px; }
        .no-border { border: none; }
        .right { text-align: right; }
        .center { text-align: center; }
        .summary { background: #f5f5f5; font-weight: bold; }
    </style>
</head>
<body>

<h2>INVOICE</h2>
<h3>{{ $booking->payment->order_id }}</h3>

<br>

<table class="no-border">
    <tr class="no-border">
        <td class="no-border">
            <strong>Customer Name:</strong> {{ $booking->user_name }} <br>
            <strong>Phone:</strong> {{ $booking->user_phone }} <br>
        </td>
        <td class="no-border right">
            <strong>Invoice Date:</strong> {{ now()->format('F d, Y') }} <br>
            <strong>Payment Status:</strong> {{ strtoupper($booking->payment->payment_status) }} <br>
        </td>
    </tr>
</table>

<hr>

<h3>Booking Details</h3>

<table>
    <tr>
        <th>Booking ID</th>
        <td>#{{ $booking->id }}</td>
    </tr>
    <tr>
        <th>Shooting Date</th>
        <td>{{ \Carbon\Carbon::parse($booking->booking_date)->format('F d, Y') }}</td>
    </tr>
    <tr>
        <th>Start Time</th>
        <td>{{ \Carbon\Carbon::parse($booking->start_time)->format('H:i') }}</td>
    </tr>
    <tr>
        <th>End Time</th>
        <td>{{ \Carbon\Carbon::parse($booking->end_time)->format('H:i') }}</td>
    </tr>
    <tr>
        <th>Duration</th>
        <td>{{ $booking->duration }} hours</td>
    </tr>
</table>

<h3>Cost Breakdown</h3>

<table>
    <tr>
        <th>Description</th>
        <th class="center">Amount</th>
    </tr>

    <tr>
        <td>Base Package Price</td>
        <td class="right">Rp {{ number_format($booking->base_price) }}</td>
    </tr>

    <tr>
        <td>Additional Addons</td>
        <td class="right">Rp {{ number_format($booking->total_addons_price) }}</td>
    </tr>

    <tr class="summary">
        <td>Total Amount</td>
        <td class="right">Rp {{ number_format($booking->total_price) }}</td>
    </tr>
</table>

<p style="margin-top: 20px; text-align:center;">
    Thank you for trusting our photography service.<br>
    We look forward to capturing your best moments.
</p>

</body>
</html>
