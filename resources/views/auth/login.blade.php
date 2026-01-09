<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ env('APP_NAME') }}</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="{{ asset('vendor/adminlte/plugins/fontawesome-free/css/all.min.css') }}">
  <!-- icheck bootstrap -->
  <link rel="stylesheet" href="{{ asset('vendor/adminlte/plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}">
  <!-- Sweetalert 2 -->
  <link rel="stylesheet" href="{{ asset('vendor/adminlte/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css') }}">
  <!-- Theme style -->
  <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}">
</head>
<body class="hold-transition login-page">
<div class="login-box">
    <div class="login-logo">
        <img src="{{ asset('images/logo_ypik.webp') }}" alt="logo_ypik" height="100" />
    </div>
    <!-- /.login-logo -->

    <div class="card">
        <div class="card-header text-center">
            <span class="font-weight-bolder">{{ env('APP_NAME') }}</span>
        </div>
        <div class="card-body login-card-body">

            <form id="login-form" action="{{ url()->current() }}" method="post">
                @csrf
                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-user"></i>    
                        Email
                    </label>
                    <input type="text" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" placeholder="Masukkan email">
                    @error('email')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-key"></i>
                        Password
                    </label>
                    <input 
                        type="password" 
                        class="form-control 
                            @error('password') is-invalid @enderror
                            @if(session('auth_failed')) is-invalid @endif" 
                        id="password" 
                        name="password" 
                        placeholder="Masukkan password">
                    @error('password')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                    @if(session('auth_failed'))
                        <div class="invalid-feedback">
                            {{ session('auth_failed') }}
                        </div>
                    @endif
                </div>
                <div class="row d-flex align-items-center">
                    <div class="col-8">
                        <div class="icheck-primary">
                            <input type="checkbox" id="remember" name="remember">
                            <label for="remember">
                                Remember Me
                            </label>
                        </div>
                    </div>
                    <!-- /.col -->
                    <div class="col-4">
                        <button id="submit-form-button" type="submit" class="btn btn-sm btn-primary btn-block">
                            <i class="fas fa-sign-in-alt"></i>    
                            Sign In
                        </button>
                    </div>
                </div>
            </form>

        </div>
        <!-- /.login-card-body -->
    </div>
</div>
<!-- /.login-box -->

<!-- jQuery -->
<script src="{{ asset('vendor/adminlte/plugins/jquery/jquery.min.js') }}"></script>
<!-- Bootstrap 4 -->
<script src="{{ asset('vendor/adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<!-- AdminLTE App -->
<script src="{{ asset('vendor/adminlte/dist/js/adminlte.min.js') }}"></script>
<!-- SweetAlert2 -->
<script src="{{ asset('vendor/adminlte/plugins/sweetalert2/sweetalert2.min.js') }}"></script>
<!-- Extra JS -->
<script src="{{ asset('js/helper.js') }}"></script>
@if(session()->has('success'))
<script>
    Notification.success("{{ session()->get('success') }}");
</script>
@endif
<script>
    $('#login-form').on('submit', function() {
        $('#submit-form-button')
            .prop('disabled', true)
            .html('<i class="fas fa-spinner fa-spin"></i>');
    });
</script>
</body>
</html>