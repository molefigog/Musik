<!DOCTYPE html>
<html>

<head>
    <title>Invoice {{ $transaction->invoice_number }}</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 6px;
            position: relative;
            background: white;
        }

        body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url("{{ asset('img/logo.png') }}") no-repeat center center;
            background-size: 80%;
            opacity: 0.08;
            z-index: 0;
        }

        .content {
            position: relative;
            z-index: 1;
        }

        .header {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            padding: 10px;
            margin-bottom: 20px;
            background: url("{{ asset('img/blue.svg') }}") center center no-repeat;
            background-size: cover;
        }


        .header h2 {
            margin: 0;
        }

        .invoice-info {
            margin: 10px 0;
        }

        table.main-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            margin-top: 20px;
        }

        .main-table th,
        .main-table td {
            padding: 5px;
            border: 1px solid #ccc;
            text-align: left;
        }

        .main-table th {
            background: #e7e7e7;
        }

        .summary {
            margin-top: 20px;
        }

        .summary p {
            margin: 3px 0;
        }

        .footer {
            position: fixed;
            bottom: 6px;
            left: 6px;
            right: 6px;
            font-size: 12px;
        }

        .footer hr {
            margin: 6px 0;
            border: none;
            border-top: 1px solid #adadad;
        }

        .footer-content {
            display: flex;
            justify-content: space-between;
            gap: 40px;
        }
    </style>
</head>

<body>
    <div class="content">
        <div class="header" style="width: 100%; display: table; table-layout: fixed;">

            <div style="display: table-cell; vertical-align: top; width: 120px;">
                <img src="{{ $company->logo }}" alt="Logo" style="width: 100px; height: auto;">
            </div>
            <div
                style="display: table-cell; vertical-align: top; text-align: right; padding-left: 20px; word-wrap: break-word;">
                <h2 style="margin: 0;">{{ $company->name }}</h2>
                <p style="margin: 2px 0;">{{ $company->address }}</p>
                <p style="margin: 2px 0;">VAT: {{ $company->vat }}</p>
                <p style="margin: 2px 0;">Contact: {{ $company->phone }}</p>
                <p style="margin: 2px 0;">Email: {{ $company->email }}</p>
            </div>
        </div>

        <div class="invoice-info">
            <h3>Invoice #{{ $transaction->invoice_number }}</h3>
            <p>Date: {{ $formattedDate }}</p>
        </div>

        <table class="main-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Price (M)</th>
                    <th>Qty</th>
                    <th>Total (M)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($transaction->items as $item)
                    <tr>
                        <td>{{ $item['name'] }}</td>
                        <td>{{ number_format($item['price'], 2) }}</td>
                        <td>{{ $item['quantity'] }}</td>
                        <td>{{ number_format($item['price'] * $item['quantity'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="footer">
            <hr />

            <div class="footer-content">
                <div style="text-align: right;">>
                    <p><strong>Subtotal (Excl. VAT):</strong> M{{ number_format($exclusiveVat, 2) }}</p>
                    <p><strong>VAT (15%):</strong> M{{ number_format($vatAmount, 2) }}</p>
                    <p><strong>Total (Incl. VAT):</strong> M{{ number_format($transaction->total, 2) }}</p>
                    <p><strong>Cash Paid:</strong> M{{ number_format($transaction->cash_paid, 2) }}</p>
                    <p><strong>Change:</strong> M{{ number_format($transaction->change, 2) }}</p>
                </div>
            </div>
        </div>
</body>

</html>
