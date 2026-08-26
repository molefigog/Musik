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
            margin: 130px 40px 100px 40px;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            width: 210mm;
            height: 297mm;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            font-size: 12px;
        }



        .page {
            position: relative;
            width: 100%;
            height: 100%;
            overflow: hidden;
        }

        .wave-bg {
            position: absolute;
            top: 0;
            left: -20px;
            right: -20px;
            height: 240px;
            background: url('{{ $waveBackground }}') no-repeat center top;
            background-size: 210mm auto;
            z-index: 0;
        }

        .header-contact {
            display: flex;
            justify-content: center;
            /* this centers the <span> elements horizontally */
            align-items: center;
            width: 100%;
            /* ensure it spans the full page width */
            font-size: 16px;
            font-weight: normal;
            color: #000;
            gap: 40px;
            /* space between each item */
            position: relative;
            top: 140px;
            z-index: 2;
            text-align: center;
        }


        .header-contact span {
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        main {
            position: relative;
            padding: 200px 30px 40px;
            z-index: 1;
        }

        .content {
            position: relative;
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
    </style>
</head>

<body>
    <div class="page">
        <div class="wave-bg"></div>
        <div class="header-contact">
            <span> {{ $company->tel }}</span>
            <span> VAT: {{ $company->vat }}</span>
            <span> {{ $company->email }}</span>
        </div>
        <main>
            <div class="content">
                @yield('content')
            </div>
        </main>
    </div>
</body>

</html>
