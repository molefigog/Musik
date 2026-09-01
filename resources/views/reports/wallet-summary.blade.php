<!-- resources/views/pdf/wallet-summary.blade.php -->
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        /* Changed font size from 12px to a highly legible 11pt */
        body {
            font-family: sans-serif;
            font-size: 11pt;
            line-height: 1.4;
            color: #333333;
        }

        /* Adjusted heading sizing and margins */
        h2 {
            font-size: 18pt;
            margin-top: 0;
            margin-bottom: 5px;
        }

        .meta {
            font-size: 10pt;
            color: #666666;
            margin-bottom: 25px;
        }

        /* Forced table layout and fixed sizing */
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-bottom: 20px;
        }

        /* Increased padding for readable data spacing */
        th,
        td {
            border: 1px solid #b0b0b0;
            padding: 10px 12px;
            text-align: left;
            font-size: 10.5pt;
            vertical-align: middle;
        }

        th {
            background: #e9e9e9;
            font-weight: bold;
        }

        /* Defined column percentages to keep layout consistent */
        .col-date {
            width: 30%;
        }

        .col-item {
            width: 50%;
        }

        .col-amount {
            width: 20%;
            text-align: right;
        }

        .text-right {
            text-align: right;
        }

        /* Fixed total alignment and size */
        .total {
            text-align: right;
            font-weight: bold;
            font-size: 12pt;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <h2>{{ ucfirst($type) }} Summary</h2>
    <p class="meta">{{ $user->name }} &middot; {{ $from->format('Y-m-d') }} to {{ $to->format('Y-m-d') }}</p>

    <table>
        <thead>
            <tr>
                <th class="col-date">Date</th>
                <th class="col-item">Item</th>
                <th class="col-amount">Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    <td>{{ $row->created_at->format('Y-m-d H:i') }}</td>
                    <td>{{ $row->item_name }}</td>
                    <td class="text-right">{{ number_format($row->amount, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" style="text-align: center; color: #777;">No records for this period.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <p class="total">Total: {{ number_format($total, 2) }}</p>
</body>

</html>
