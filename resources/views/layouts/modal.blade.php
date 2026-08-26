{{-- <div class="modal fade bs-example-modal-center" tabindex="-1" role="dialog"
aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">login</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">

                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <!-- Email input -->
                    <div class="mb-3">
                        <label for="email" class="form-label">Email address</label>
                        <div class="input-group mb-2">
                            <div class="input-group-prepend">
                                <div class="input-group-text"><i class="icon-mail_outline"></i></div>
                            </div>
                            <input id="login" type="text" placeholder="Enter Email"
                                class="form-control @error('login') is-invalid @enderror" name="login"
                                value="{{ old('login') }}" required autocomplete="login" autofocus>
                            @error('login')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                    </div>

                    <!-- Password input -->
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group mb-2">
                            <div class="input-group-prepend">
                                <div class="input-group-text">
                                    <i id="togglePassword" class="icon-lock_outline"></i>
                                </div>
                            </div>
                            <input type="password" id="password2" placeholder="Enter Password"
                                class="form-control @error('password') is-invalid @enderror" name="password" required
                                autocomplete="new-password">
                            <div class="input-group-prepend">
                                <div class="input-group-text">
                                    <i id="togglePassword1" class="icon-eye"></i>
                                </div>
                            </div>
                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <!-- Remember Me checkbox -->
                    <div class="mb-3 form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="basic_checkbox_1"
                            {{ old('remember') ? 'checked' : '' }}>
                        {{-- <input type="checkbox" class="form-check-input" id="rememberMe">
                        <label class="form-check-label" for="rememberMe">Remember Me</label>
                    </div>

                    <div class="row mb-3">
                        <div class="col text-end">
                            @if (Route::has('password.request'))
                                <a class="text-muted" href="{{ route('password.request') }}">
                                    <i class="mdi mdi-lock"></i> {{ __('Forgot Your Password?') }}
                                </a>
                            @endif
                        </div>
                        <div class="col text-end">
                            <span>or</span>
                            <a href="{{ url('/admin/register') }}">sign up for an account</a>
                        </div>
                    </div>

                    <!-- Login button -->
                    <button type="submit" class="btn btn-primary w-100">Login</button>
                </form>
            </div>
            <div class="modal-footer">


            </div>
        </div>
    </div>
</div> --}}


<div class="col-sm-6 col-md-4 col-xl-3">
    {{-- <div class="my-4 text-center">

        <button type="button" class="btn btn-primary waves-effect waves-light"
            data-bs-toggle="modal" data-bs-target=".bs-example-modal-center">login</button>
    </div> --}}

    <div class="modal fade bs-example-modal-center" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Login</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="input-group has-validation mb-3">
                            <span class="input-group-text"><i class="ion ion-md-mail"></i></span>
                            <div class="form-floating">
                              <input type="email"  name="login" class="form-control" id="floatingInputGroup2" value="{{old('email')}}" placeholder="Email" required autocomplete="login" autofocus>
                              <label for="floatingInputGroup2">Email address</label>
                            </div>
                            @error('login')
                            <div class="invalid-feedback">
                                <strong>{{ $message }}</strong>
                            </div>
                            @enderror
                        </div>

                        <div class="input-group has-validation mb-3">
                            <span class="input-group-text"><i class="ion ion-ios-lock"></i></span>
                            <div class="form-floating">
                              <input type="password" name="password" class="form-control" id="password2" placeholder="Email" required autocomplete="login" autofocus>
                              <label for="floatingInputGroup2">Password</label>
                            </div>
                            <span class="input-group-text"><i id="togglePassword1" class="ion ion-md-eye"></i></span>
                            @error('password')
                            <div class="invalid-feedback">
                                <strong>{{ $message }}</strong>
                            </div>
                            @enderror
                        </div>

                        <!-- Remember Me checkbox -->
                        <div class="mb-3 form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="basic_checkbox_1"
                                {{ old('remember') ? 'checked' : '' }}>
                            {{-- <input type="checkbox" class="form-check-input" id="rememberMe"> --}}
                            <label class="form-check-label" for="rememberMe">Remember Me</label>
                        </div>

                        <div class="row mb-3">
                            <div class="col text-end">
                                @if (Route::has('password.request'))
                                    <a class="text-muted" href="{{ route('password.request') }}">
                                        <i class="mdi mdi-lock"></i> {{ __('Forgot Your Password?') }}
                                    </a>
                                @endif
                            </div>
                            <div class="col text-end">
                                <span>or</span>
                                <a href="{{ url('/admin/register') }}">sign up for an account</a>
                            </div>
                        </div>

                        <!-- Login button -->
                        <button type="submit" class="btn btn-primary w-100">Login</button>
                    </form>
                    <hr>
                    <a href="{{ route('google.redirect') }}" class="btn btn-primary btn-sm text-center"><i class="fab fa-google"></i>  Login with Google </a>
                    {{-- <a href="{{ route('facebook.login') }}" class="btn btn-primary btn-sm text-center">
                        <i class="fab fa-facebook-f fa-fw"></i>
                        Login with Facebook
                     </a> --}}
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
    <!-- /.modal -->
</div>

@auth
<div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" style="display: none;" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            @livewire('cashout')

        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
@endauth
<div class="modal fade" id="exampleModal2" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content bg-dark text-white">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Register</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">

                {{-- @livewire('register') --}}
                @livewire('register2')
            </div>
            <div class="modal-footer">


            </div>
        </div>
    </div>
</div>

@push('modal')
    <script>
        document.getElementById('togglePassword').addEventListener('click', function() {
            var passwordInput = document.getElementById('password2');
            var icon = document.getElementById('togglePassword');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('ion ion-md-eye');
                icon.classList.add('ion ion-md-eye-off');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('ion ion-md-eye-off');
                icon.classList.add('on ion-md-eye');
            }
        });
    </script>
    <script>
        document.getElementById('togglePassword1').addEventListener('click', function() {
            var passwordInput = document.getElementById('password2');
            var icon = document.getElementById('togglePassword1');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('ion ion-md-eye');
                icon.classList.add('ion ion-md-eye-off');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('ion ion-md-eye-off');
                icon.classList.add('on ion-md-eye');
            }
        });
    </script>
@endpush
