@php
    $authMethod = \Illuminate\Support\Facades\Schema::hasTable('settings') ? \App\Models\Setting::where('key', 'auth_method')->value('value') : 'internal';
@endphp

@extends('layouts.guest')

@section('content')
    @if($authMethod === 'sso')
        @if(request()->has('logged_out'))
        <div class="card-body login-card-body text-center py-5">
            <div class="mb-3">
                <i class="fas fa-check-circle text-success fa-3x"></i>
            </div>
            <h4>Anda telah keluar</h4>
            <p class="text-muted">Sesi Anda telah berakhir. Mengarahkan kembali ke halaman login SSO...</p>
            <!-- <a href="{{ route('sso.login') }}" class="btn btn-primary btn-block mt-4">
                <i class="fas fa-sign-in-alt mr-2"></i> Masuk Kembali via SSO
            </a> -->
            
            <script>
                setTimeout(function() {
                    window.location.href = "{{ route('sso.login') }}";
                }, 2000);
            </script>
        </div>
        @else
        <div class="card-body login-card-body text-center py-5">
            <h4>Mengarahkan ke SSO...</h4>
            <p class="text-muted">Mohon tunggu sebentar, Anda sedang dialihkan.</p>
            <div class="spinner-border text-primary mt-3" role="status">
                <span class="sr-only">Loading...</span>
            </div>
            <script>
                setTimeout(function() {
                    window.location.href = "{{ route('sso.login') }}";
                }, 1000);
            </script>
        </div>
        @endif
    @else
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
    @endif
    <!-- /.login-card-body -->
@endsection
