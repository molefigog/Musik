<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    @php
        $company = App\Models\Company::first();
        $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents(public_path('img/logo.png')));
        $waveBackground =
            'data:image/png;base64,' . base64_encode(file_get_contents(public_path('img/upper_third.png')));
    @endphp

    <style>
        @page {
            margin: 130pt 40pt 100pt 40pt;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            font-size: 11pt;
            height: 100%;
        }

        body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 210mm;
            height: 297mm;
            background: url('{{ $logoBase64 }}') no-repeat center center;
            background-size: 80%;
            opacity: 0.06;
            z-index: 0;
        }

        .wave-bg {
            position: fixed;
            top: -2mm;
            left: 0;
            right: 0;
            height: 80mm;
            /* Adjust to match wave height */
            width: 100%;
            background: url('{{ $waveBackground }}') no-repeat center top;
            background-size: 210mm auto;
            z-index: -1;
        }

        .header-contact {
            position: relative;
            margin-top: 38mm;
            /* should sit just under the wave */
            display: flex;
            justify-content: center;
            gap: 40px;
            font-size: 11pt;
            text-align: center;
            z-index: 1;
        }

        .content {
            position: relative;
            padding: 10pt 30pt;
            z-index: 1;
        }

        table.main-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .main-table th,
        .main-table td {
            padding: 6px;
            border: 1px solid #ddd;
            text-align: left;
        }

        .main-table th {
            background: #f0f0f0;
        }

        .footer {
            position: fixed;
            bottom: 30pt;
            left: 40pt;
            right: 40pt;
            font-size: 9pt;
            text-align: center;
            border-top: 1px solid #ccc;
            padding-top: 5pt;
        }
    </style>
</head>

<body>
    <!-- Wave background placed outside of margins, behind all content -->
    <div class="wave-bg"></div>

    <!-- Contact info under wave -->
    <div class="header-contact">
        <span>Tel: {{ $company->tel }}</span>
        <span>VAT: {{ $company->vat }}</span>
        <span>Email: {{ $company->email }}</span>
    </div>

    <!-- Report Content -->
    <div class="content">
        @yield('content')
    </div>

    <!-- Footer -->
    <div class="footer">
        {{ $company->name }} – {{ $company->address }} – {{ $company->email }}
    </div>

</body>

</html>
