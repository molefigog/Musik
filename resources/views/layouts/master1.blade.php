<!doctype html>
<html lang="en" data-bs-theme="dark">

<head>

    <meta charset="utf-8">
    <meta name="author" content="gog">
    <meta name="MobileOptimized" content="320">
    <meta property="og:site_name" content="gw-ent" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="canonical" href="{{ config('app.url') }}">
    @yield('head')

    <!-- Bootstrap Css -->
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" id="bootstrap-style" rel="stylesheet" type="text/css">
    <!-- Icons Css -->
    <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css">
    <!-- App Css-->
    <link href="{{ asset('assets/css/app.min.css') }}" id="app-style" rel="stylesheet" type="text/css">
    <!-- Scripts -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/6.6.9/sweetalert2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css">
    @if ($setting && $setting->favicon_url)
        <link rel="icon" type="image/png" href="{{ $setting->favicon_url }}">
    @endif
    @if ($setting && $setting->favicon_url)
        <link rel="apple-touch-icon" href="{{ $setting->favicon_url }}">
    @endif
    @if ($setting && $setting->favicon_url)
        <meta name="msapplication-TileImage" content="{{ $setting->favicon_url }}">
    @endif
    @if ($setting && $setting->favicon_url)
        <link rel="shortcut icon" href="{{ $setting->favicon_url }}">
    @endif
    @stack('aplayer')
    <style>
        .dropdown-menu {
            --bs-dropdown-bg: #36394cf2;
        }
        #page-topbar {
    position: fixed;
    top: 0;
    right: 0;
    left: 0;
    z-index: 1002;
    background-color: #353e53;
}
    </style>
@livewireStyles

</head>

{{-- @php
    $artists = App\Models\User::orderBy('name')->get();
@endphp --}}

<body data-topbar="dark"data-layout="horizontal">
    <!-- Begin page -->
    <div id="layout-wrapper">

        @include('layouts.header')

        <div class="main-content">


            <hr>

            <div class="page-content">
                <div class="container-fluid text-center">
                    <div class="d-block d-sm-none">
                        @yield('search')
                       </div>

                    {{ $slot }}
                    @include('layouts.modal')

                </div> <!-- container-fluid -->
            </div>
            <!-- End Page-content -->

            @yield('audio')

            <footer class="footer">
                <div class="container-fluid">
                    <p class="text center">Select Songs By Artist</p>
                    <ul class="nav">
                        @foreach ($artists as $artist)
                            @php
                                $musicCount = DB::table('music_user')
                                    ->where('user_id', $artist->id)
                                    ->count();
                            @endphp

                            @if ($musicCount > 0)
                                <li class="nav-item text-uppercase">
                                    <div class="p-2">
                                        <a class="btn btn-outline-info btn-sm text-truncate"
                                            href="{{ url('artist', str_replace(' ', '_', $artist->name)) }}"
                                            style="max-width: 150px;">
                                            {{ $artist->name }}
                                        </a>
                                    </div>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                    <div class="row">
                        <div class="col-12">
                            ©
                            <script>
                                document.write(new Date().getFullYear())
                            </script>{{ $setting->site }}<span class="d-none d-sm-inline-block"> | <i
                                    class="mdi mdi-heart text-danger"></i> Help <a href="mailto:{{ $admin->email }}"
                                    target="_blank">Desk</a></span>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
        <!-- end main content-->
    </div>
    <!-- END layout-wrapper -->

    <!-- Right Sidebar -->
    {{-- <div class="right-bar">
        <div data-simplebar class="h-100">
            <div class="rightbar-title px-3 py-4">
                <a href="javascript:void(0);" class="right-bar-toggle float-end">
                    <i class="mdi mdi-close noti-icon"></i>
                </a>
                <h5 class="m-0">Settings</h5>
            </div>
            <!-- Settings -->
            <hr class="mt-0" />

            <div class="p-4">

                <div class="form-check form-switch mb-3">
                    <input type="checkbox" class="form-check-input theme-choice" id="light-mode-switch" checked />
                    <label class="form-check-label" for="light-mode-switch">Light Mode</label>
                </div>
                <div class="form-check form-switch mb-3">
                    <input type="checkbox" class="form-check-input theme-choice" id="dark-mode-switch"
                        data-bsStyle="{{ asset('assets/css/bootstrap-dark.min.css') }}"
                        data-appStyle="{{ asset('assets/css/app-dark.min.css') }}" />
                    <label class="form-check-label" for="dark-mode-switch">Dark Mode</label>
                </div>
            </div>

        </div> <!-- end slimscroll-menu-->
    </div> --}}

    @stack('modals')

    <script src="{{ asset('assets/libs/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/libs/metismenu/metisMenu.min.js') }}"></script>
    <script src="{{ asset('assets/libs/simplebar/simplebar.min.js') }}"></script>
    <script src="{{ asset('assets/libs/node-waves/waves.min.js') }}"></script>
    <!-- Peity chart-->
    <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('assets/libs/peity/jquery.peity.min.js') }}"></script>
    <script>
        const BootstrapMin = "{{ asset('assets/css/bootstrap.min.css') }}";
        const AppCss = "{{ asset('assets/css/app.min.css') }}";
        const BootstrapRtl = "{{ asset('assets/css/bootstrap-rtl.min.css') }}";
        const AppRtl = "{{ asset('assets/css/app-rtl.min.css') }}";
    </script>
    {{-- <script src="{{ asset('assets/js/app.js') }}"></script> --}}
    @stack('player')

    @stack('modal')
    @stack('pal')
    @stack('upload_status')
    @stack('updated')
    @stack('mpesa')

    @livewireScripts
</body>

</html>
