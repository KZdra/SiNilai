@extends('layouts.guest')

@section('content')
    <div class="card-body login-card-body">
        <p class="login-box-msg">Silakan masuk untuk mengakses Sistem Informasi Nilai</p>

        <form action="{{ route('login') }}" method="post">
            @csrf

            <div class="input-group mb-3">
                <input type="username" name="username" class="form-control @error('username') is-invalid @enderror" placeholder="{{ __('username') }}" required autofocus>
                <div class="input-group-append">
                    <div class="input-group-text">
                        <span class="fas fa-user"></span>
                    </div>
                </div>
                @error('username')
                <span class="error invalid-feedback">
                    {{ $message }}
                </span>
                @enderror
            </div>

            <div class="input-group mb-3">
                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="{{ __('Password') }}" required>
                <div class="input-group-append">
                    <div class="input-group-text">
                        <span class="fas fa-lock"></span>
                    </div>
                </div>
                @error('password')
                <span class="error invalid-feedback">
                    {{ $message }}
                </span>
                @enderror
            </div>

            <div class="row align-items-center mb-3">
                <div class="col-8">
                    <!-- <div class="icheck-primary">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember">
                            {{ __('Remember Me') }}
                        </label>
                    </div> -->
                </div>
                <!-- /.col -->
                <div class="col-12">
                    <button type="submit" class="btn btn-primary btn-block">{{ __('Masuk') }}</button>
                </div>
                <!-- /.col -->
            </div>
        </form>

        <!-- @if (Route::has('password.request'))
            <p class="mb-1 text-center">
                <a href="{{ route('password.request') }}">{{ __('Lupa Password?') }}</a>
            </p>
        @endif -->
    </div>
    <!-- /.login-card-body -->
@endsection
