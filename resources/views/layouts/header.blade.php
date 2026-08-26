<header id="page-topbar">
    <div class="navbar-header">
        <div class="d-flex">
            <!-- LOGO -->
            <div class="navbar-brand-box"  style="color: #353e53;">
                <a href="/" class="logo logo-dark">
                    {{-- <span class="logo-sm">
                        <img src="{{ $setting->logo_url }}" alt="" class="img-fluid" style="width:150px;">
                    </span>
                    <span class="logo-lg">
                        <img src="{{ $setting->logo_url }}" alt="" class="img-fluid" style="width:150px;">
                    </span>
                </a>

                <a href="/" class="logo logo-light">
                    <span class="logo-sm">
                        <img src="{{ $setting->logo_url }}" alt="" class="img-fluid" style="width:150px;">
                    </span>
                    <span class="logo-lg">
                        <img src="{{ $setting->logo_url }}" alt="" class="img-fluid" style="width:150px;">
                    </span>
                </a> --}}
            </div>

            <button type="button" class="btn btn-sm me-2 font-size-24 d-lg-none header-item waves-effect waves-light"
                data-bs-toggle="collapse" data-bs-target="#topnav-menu-content">
                <i class="mdi mdi-menu"></i>
            </button>

        </div>

        <div class="d-flex">

            <!-- App Search-->
            <div class=" d-none d-lg-block">
             @yield('search')
            </div>

            @if (Auth::check())
                <div class="dropdown d-inline-block">
                    <button type="button" class="btn header-item waves-effect" id="page-header-user-dropdown"
                        data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <img class="rounded-circle header-profile-user" src="{{ Auth::user()->avatar_url }}"
                            alt="Header Avatar">
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <!-- item-->
                        <a class="dropdown-item" href="{{ url('/admin/profile') }}"><i
                                class="mdi mdi-account-circle font-size-17 align-middle me-1"></i> Profile

                        </a>
                        <a class="dropdown-item" href="" data-bs-toggle="modal" data-bs-target="#staticBackdrop">
                            {{-- <span class="badge bg-success float-end"></span> --}}
                            <i class="mdi mdi-wallet font-size-17 align-middle me-1"></i>R{{ Auth::user()->balance }}
                        </a>

                        <a class="dropdown-item d-block" href="{{ url('/admin/music/create') }}">


                            <i class="mdi mdi-upload font-size-17 align-middle me-1"></i>
                            Upload
                        </a>
                        <a class="dropdown-item" href="{{ url('/admin/bookings') }}">
                            <i class="mdi mdi-lock-open-outline font-size-17 align-middle me-1"></i> Bookings
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item text-danger" href="{{ route('logout') }}"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="bx bx-power-off font-size-17 align-middle me-1 text-danger"></i> Logout</a>

                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>

                    </div>
                </div>
            @else
                <div class="dropdown d-inline-block">
                    <button type="button" class="btn header-item waves-effect" id="page-header-user-dropdown"
                        data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <img class="rounded-circle header-profile-user"
                            src="{{ asset('storage/avatars/default_avatar.png') }}" alt="Header Avatar">
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <!-- item-->
                        <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target=".bs-example-modal-center">
                            <i class="mdi mdi-account-circle font-size-17 align-middle me-1"></i>
                            Login
                        </a>
                        <a class="dropdown-item" href="{{url('/admin/register')}}">

                            <i class="mdi mdi-wallet font-size-17 align-middle me-1"></i>
                            Register
                        </a>

                    </div>
                </div>
            @endif
            {{-- <div class="dropdown d-inline-block">
                <button type="button" class="btn header-item noti-icon right-bar-toggle waves-effect">
                    <i class="mdi mdi-cog-outline"></i>
                </button>
            </div> --}}

        </div>
    </div>
</header>

<div class="topnav">
    <div class="container-fluid">
        <nav class="navbar navbar-light navbar-expand-lg topnav-menu">

            <div class="collapse navbar-collapse" id="topnav-menu-content">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="/">
                            <i class="ti-home me-2"></i>Home
                        </a>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle arrow-none" href="#" id="topnav-emailtemplates"
                            role="button">
                            <i class="ti-bookmark-alt me-2"></i>Genres
                        </a>
                        <div class="dropdown-menu" aria-labelledby="topnav-emailtemplates">
                            @foreach ($genres as $genre)
                                <a class="dropdown-item"
                                    href="{{ route('songs-by-genre', urlencode($genre)) }}">{{ $genre }}
                                </a>
                            @endforeach
                        </div>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('getdownloads') }}">
                            <i class="fas fa-cloud-download-alt me-2"></i>Downloads
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('about') }}">
                            <i class="fas fa-tasks me-2"></i>About
                        </a>
                    </li>
                    <li class="nav-item"><a href="https://www.facebook.com/elliot.gog" target="_blank"
                        class="nav-link"><span class="fab fa-facebook"></span></a></li>

                <li class="nav-item"><a href="mailto:molefigw@gmail.com" target="_blank" class="nav-link"><span
                            class="far fa-envelope"></span></a></li>
                </ul>
            </div>
        </nav>
    </div>
</div>
