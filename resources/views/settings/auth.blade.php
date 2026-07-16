@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Pengaturan Otentikasi (Auth)</h1>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-8">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Konfigurasi SSO & Login</h3>
                        </div>
                        <form action="{{ route('settings.auth.update') }}" method="POST">
                            @csrf
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Metode Login</label>
                                    <div class="custom-control custom-radio">
                                        <input class="custom-control-input" type="radio" id="authInternal" name="auth_method" value="internal" {{ ($settings['auth_method'] ?? 'internal') == 'internal' ? 'checked' : '' }}>
                                        <label for="authInternal" class="custom-control-label">Internal (Username & Password Lokal)</label>
                                    </div>
                                    <div class="custom-control custom-radio">
                                        <input class="custom-control-input" type="radio" id="authSso" name="auth_method" value="sso" {{ ($settings['auth_method'] ?? 'internal') == 'sso' ? 'checked' : '' }}>
                                        <label for="authSso" class="custom-control-label">SSO (Single Sign-On)</label>
                                    </div>
                                </div>

                                <hr>
                                
                                <h5 class="mt-4 mb-3">Konfigurasi SSO (Hanya berlaku jika SSO dipilih)</h5>

                                <div class="form-group">
                                    <label for="sso_server_url">SSO Server URL</label>
                                    <input type="url" class="form-control" id="sso_server_url" name="sso_server_url" value="{{ old('sso_server_url', $settings['sso_server_url'] ?? '') }}" placeholder="Contoh: http://localhost:8000">
                                </div>

                                <div class="form-group">
                                    <label for="sso_client_id">Client ID</label>
                                    <input type="text" class="form-control" id="sso_client_id" name="sso_client_id" value="{{ old('sso_client_id', $settings['sso_client_id'] ?? '') }}">
                                </div>

                                <div class="form-group">
                                    <label for="sso_client_secret">Client Secret</label>
                                    <input type="text" class="form-control" id="sso_client_secret" name="sso_client_secret" value="{{ old('sso_client_secret', $settings['sso_client_secret'] ?? '') }}">
                                </div>

                                <div class="form-group">
                                    <label for="sso_redirect_uri">Redirect URI / Callback</label>
                                    <input type="url" class="form-control" id="sso_redirect_uri" name="sso_redirect_uri" value="{{ old('sso_redirect_uri', $settings['sso_redirect_uri'] ?? '') }}" placeholder="Contoh: http://localhost:8001/auth/callback">
                                </div>
                            </div>

                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
