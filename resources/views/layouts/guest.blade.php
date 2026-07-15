<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('css/fontawesome.min.css') }}">
    <!-- icheck bootstrap -->
    <link rel="stylesheet" href="{{ asset('css/icheck-bootstrap.min.css') }}">
    <!-- Theme style -->
    <link rel="stylesheet" href="{{ asset('css/adminlte.min.css') }}">

    <style>
        body.login-page {
            background: linear-gradient(135deg, #f5f7fb 0%, #e4ecf5 100%) !important;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Source Sans Pro', sans-serif;
        }

        .login-box {
            width: 100% !important;
            max-width: 400px !important;
            margin: 20px auto !important;
        }

        .login-logo a {
            color: #2c3e50 !important;
            font-weight: 700 !important;
            font-size: 2.2rem !important;
            letter-spacing: -0.5px;
        }

        .login-logo a span {
            color: #007bff;
        }

        .login-box .card {
            border-radius: 12px !important;
            border: 1px solid rgba(0, 0, 0, 0.08) !important;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04) !important;
            background: #ffffff !important;
        }

        .login-card-body {
            border-radius: 12px !important;
            padding: 2.2rem 2rem !important;
            background: #ffffff !important;
            color: #495057 !important;
        }

        .login-box-msg {
            color: #6c757d !important;
            font-size: 0.95rem !important;
            margin-bottom: 1.8rem !important;
            padding: 0 !important;
        }

        /* Form Controls */
        .input-group {
            margin-bottom: 1.25rem !important;
        }

        .form-control {
            border-radius: 8px !important;
            height: 44px !important;
            border: 1px solid #ced4da !important;
            font-size: 0.95rem !important;
            transition: all 0.2s ease !important;
            border-right: none !important;
        }

        .form-control:focus {
            border-color: #80bdff !important;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.15) !important;
        }

        .input-group-append .input-group-text {
            border-top-right-radius: 8px !important;
            border-bottom-right-radius: 8px !important;
            border: 1px solid #ced4da !important;
            border-left: none !important;
            background-color: #f8f9fa !important;
            color: #6c757d !important;
            height: 44px !important;
            transition: all 0.2s ease !important;
        }

        /* Sync focus borders between input and icon append */
        .input-group .form-control:focus + .input-group-append .input-group-text {
            border-color: #80bdff !important;
        }

        .form-control.is-invalid {
            border-color: #dc3545 !important;
        }

        .form-control.is-invalid + .input-group-append .input-group-text {
            border-color: #dc3545 !important;
        }

        .invalid-feedback {
            font-size: 0.85rem !important;
            margin-top: 4px !important;
        }

        /* Button Styling */
        .btn-primary {
            background-color: #007bff !important;
            border-color: #007bff !important;
            border-radius: 8px !important;
            height: 44px !important;
            font-weight: 600 !important;
            font-size: 0.95rem !important;
            transition: all 0.2s ease !important;
        }

        .btn-primary:hover {
            background-color: #0069d9 !important;
            border-color: #0062cc !important;
        }

        /* Checkbox adjustments */
        .icheck-primary label {
            font-weight: 500 !important;
            color: #495057 !important;
            font-size: 0.9rem !important;
        }

        /* Responsive Mobile Adjustments */
        @media (max-width: 480px) {
            .login-card-body {
                padding: 1.8rem 1.25rem !important;
            }
            .d-flex.justify-content-between.align-items-center.mb-4 {
                flex-direction: column;
                gap: 12px;
                align-items: flex-start !important;
            }
        }
    </style>
</head>
<body class="hold-transition login-page">
<div class="login-box">
    <div class="login-logo">
        <a href="/">Si<span>Nilai</span></a>
    </div>
    <!-- /.login-logo -->
    <div class="card border-0">
        @yield('content')
    </div>
</div>
<!-- /.login-box -->

@vite('resources/js/app.js')
<!-- Bootstrap 4 -->
<script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
<!-- AdminLTE App -->
<script src="{{ asset('js/adminlte.min.js') }}" defer></script>
</body>
</html>
