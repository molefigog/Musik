<!doctype html>
<html lang="en" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta property="og:site_name" content="gw-ent" />
    <meta name="description" content="@yield('meta_description', 'Genius Works Entertainment platform')">
    <title>@yield('title', 'GW-ENT')</title>

    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/fontawesome-v6.css') }}">
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    @livewireStyles
</head>

<body>
    <div class="container-fluid text-center">
        @yield('content')
    </div>

    <script src="{{ asset('js/bootstrap.bundle.min.js') }}" defer></script>
    @stack('pesa')
    @livewireScripts
</body>

</html>
