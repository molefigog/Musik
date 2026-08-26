<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice</title>
</head>

<body style="font-family: Arial, sans-serif; color: #111827; line-height: 1.5;">
    <h2 style="margin-bottom: 0;">Payment Invoice</h2>
    <p style="margin-top: 4px; color: #4b5563;">Thank you for your purchase,
        {{ $invoice['customer_name'] ?? 'Customer' }}.</p>

    <p><strong>Transaction ID:</strong> {{ $invoice['txn_id'] ?? 'N/A' }}</p>
    <p><strong>Payment Type:</strong> {{ strtoupper($invoice['type'] ?? 'N/A') }}</p>
    <p><strong>Issued At:</strong> {{ $invoice['issued_at'] ?? now()->toDateTimeString() }}</p>

    <h3 style="margin-top: 24px;">Items</h3>
    <table width="100%" cellpadding="8" cellspacing="0" border="1"
        style="border-collapse: collapse; border-color: #e5e7eb;">
        <thead style="background: #f9fafb;">
            <tr>
                <th align="left">Item</th>
                <th align="left">Music ID</th>
                <th align="right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice['items'] ?? [] as $item)
                <tr>
                    <td>{{ $item['name'] ?? 'Item' }}</td>
                    <td>{{ $item['music_id'] ?? '-' }}</td>
                    <td align="right">{{ number_format((float) ($item['amount'] ?? 0), 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p style="margin-top: 16px;">
        <strong>Total:</strong>
        {{ $invoice['currency'] ?? 'LSL' }} {{ number_format((float) ($invoice['total'] ?? 0), 2) }}
    </p>

    <p style="color: #6b7280; font-size: 12px; margin-top: 24px;">
        This is an automated invoice email. Please keep it for your records.
    </p>
</body>

</html>
