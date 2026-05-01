<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="color-scheme" content="light dark">
  <title>{{ __('app.app_name') }}</title>

  <script>
      (function () {
          try {
              const storedTheme = localStorage.getItem('soy-ypik-theme');
              const theme = storedTheme === 'dark' ? 'dark' : 'light';
              document.documentElement.dataset.theme = theme;
              document.documentElement.style.colorScheme = theme;
          } catch (error) {
              document.documentElement.dataset.theme = 'light';
              document.documentElement.style.colorScheme = 'light';
          }
      })();
  </script>

  <!-- Google Fonts -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="{{ asset('vendor/adminlte/plugins/fontawesome-free/css/all.min.css') }}">
  <!-- icheck bootstrap -->
  <link rel="stylesheet" href="{{ asset('vendor/adminlte/plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}">
  <!-- Sweetalert 2 -->
  <link rel="stylesheet" href="{{ asset('vendor/adminlte/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css') }}">
  <!-- Theme style -->
  <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}">
  <!-- Extras -->
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">
  <style>
      :root {
          --ypk-blue: #1a56db;
          --ypk-blue-dark: #1e3a8a;
          --ypk-blue-soft: #dbeafe;
          --ypk-text: #0f172a;
          --ypk-muted: #64748b;
          --ypk-border: #e2e8f0;
          --ypk-bg: #f1f5f9;
          --ypk-card: #ffffff;
          --ypk-shadow: 0 22px 60px rgba(15, 23, 42, 0.18);
      }

      body.ypk-login {
          font-family: 'Plus Jakarta Sans', 'Source Sans Pro', sans-serif;
          background: radial-gradient(circle at 10% 10%, #e0f2fe, transparent 50%),
                      radial-gradient(circle at 90% 15%, #e9d5ff, transparent 45%),
                      radial-gradient(circle at 50% 100%, #dbeafe, transparent 55%),
                      var(--ypk-bg);
          color: var(--ypk-text);
      }

      .ypk-login-wrapper {
          min-height: 100vh;
          display: flex;
          align-items: center;
          justify-content: center;
          padding: 36px 20px;
      }

      .ypk-login-card {
          width: 100%;
          max-width: 980px;
          background: var(--ypk-card);
          border-radius: 24px;
          overflow: hidden;
          box-shadow: var(--ypk-shadow);
          border: 1px solid var(--ypk-border);
          display: grid;
          grid-template-columns: 1.15fr 1fr;
      }

      .ypk-login-visual {
          padding: 40px 38px;
          background: linear-gradient(135deg, var(--ypk-blue-dark) 0%, var(--ypk-blue) 55%, #2563eb 100%);
          color: #fff;
          position: relative;
          overflow: hidden;
      }

      .ypk-login-visual::after {
          content: '';
          position: absolute;
          width: 240px;
          height: 240px;
          border-radius: 40px;
          background: rgba(255, 255, 255, 0.08);
          top: -40px;
          right: -60px;
          transform: rotate(18deg);
      }

      .ypk-login-visual::before {
          content: '';
          position: absolute;
          width: 180px;
          height: 180px;
          border-radius: 50%;
          background: rgba(255, 255, 255, 0.12);
          bottom: -60px;
          left: -40px;
      }

      .ypk-brand {
          display: flex;
          align-items: center;
          gap: 16px;
          margin-bottom: 26px;
          position: relative;
          z-index: 1;
      }

      .ypk-brand img {
          width: 58px;
          height: 58px;
          border-radius: 16px;
          padding: 6px;
          background: rgba(255, 255, 255, 0.18);
          box-shadow: 0 8px 18px rgba(0, 0, 0, 0.18);
      }

      .ypk-brand h1 {
          font-size: 20px;
          font-weight: 800;
          margin: 0;
          letter-spacing: -0.2px;
      }

      .ypk-brand p {
          margin: 4px 0 0;
          font-size: 13px;
          opacity: 0.85;
      }

      .ypk-visual-title {
          font-size: 26px;
          font-weight: 700;
          margin-bottom: 10px;
          position: relative;
          z-index: 1;
      }

      .ypk-visual-subtitle {
          font-size: 14px;
          opacity: 0.9;
          margin-bottom: 24px;
          position: relative;
          z-index: 1;
      }

      .ypk-visual-list {
          list-style: none;
          padding: 0;
          margin: 0;
          display: grid;
          gap: 12px;
          position: relative;
          z-index: 1;
      }

      .ypk-visual-list li {
          display: flex;
          align-items: center;
          gap: 10px;
          font-size: 14px;
          background: rgba(255, 255, 255, 0.12);
          padding: 10px 12px;
          border-radius: 12px;
          border: 1px solid rgba(255, 255, 255, 0.18);
      }

      .ypk-visual-list i {
          font-size: 16px;
      }

      .ypk-login-form {
          padding: 40px 38px;
      }

      .ypk-login-form h2 {
          font-size: 22px;
          font-weight: 700;
          margin-bottom: 6px;
      }

      .ypk-login-form p {
          color: var(--ypk-muted);
          margin-bottom: 22px;
          font-size: 14px;
      }

      .ypk-login-form label {
          font-weight: 600;
          font-size: 13px;
          color: var(--ypk-text);
      }

      .ypk-login-form .form-control {
          border-radius: 12px;
          border: 1px solid var(--ypk-border);
          background: #f8fafc;
          font-size: 14px;
          padding: 12px 14px;
      }

      .ypk-login-form .form-control:focus {
          border-color: var(--ypk-blue);
          box-shadow: 0 0 0 3px rgba(26, 86, 219, 0.18);
          background: #ffffff;
      }

      .ypk-login-form .btn-primary {
          background: linear-gradient(135deg, var(--ypk-blue-dark), var(--ypk-blue));
          border: none;
          font-weight: 600;
          padding: 12px 18px;
          border-radius: 12px;
          box-shadow: 0 12px 20px rgba(26, 86, 219, 0.2);
      }

      .ypk-login-form .btn-primary:hover {
          background: linear-gradient(135deg, #1e40af, #1d4ed8);
      }

      .ypk-login-form .icheck-primary > label {
          font-size: 13px;
          color: var(--ypk-muted);
      }

      .ypk-login-form .invalid-feedback {
          font-size: 12px;
      }

      .ypk-login-footer {
          margin-top: 22px;
          font-size: 12px;
          color: var(--ypk-muted);
      }

      .ypk-login-footer strong {
          color: var(--ypk-text);
      }

      @media (max-width: 991px) {
          .ypk-login-card {
              grid-template-columns: 1fr;
          }

          .ypk-login-visual {
              order: 2;
              border-top: 1px solid rgba(255, 255, 255, 0.2);
          }
      }

      @media (max-width: 576px) {
          .ypk-login-visual,
          .ypk-login-form {
              padding: 28px 24px;
          }
      }
  </style>
</head>
<body class="hold-transition login-page ypk-login">
<script>
    (function () {
        const theme = document.documentElement.dataset.theme === 'dark' ? 'dark' : 'light';
        document.body.dataset.theme = theme;
        document.body.classList.toggle('dark-mode', theme === 'dark');
    })();
</script>
<div class="ypk-login-wrapper">
    <div class="ypk-login-card">
        <div class="ypk-login-visual">
            <div class="ypk-brand">
                <img src="{{ asset('images/logo_ypik.webp') }}" alt="logo_ypik">
                <div>
                    <h1>{{ __('app.app_name') }}</h1>
                    <p>{{ __('app.brand_short') }}</p>
                </div>
            </div>
            <div class="ypk-visual-title">{{ __('app.auth.portal_title') }}</div>
            <div class="ypk-visual-subtitle">
                {{ __('app.auth.portal_subtitle') }}
            </div>
            <ul class="ypk-visual-list">
                <li><i class="fas fa-shield-alt"></i> {{ __('app.auth.safe_access') }}</li>
                <li><i class="fas fa-chart-line"></i> {{ __('app.auth.summary_ready') }}</li>
                <li><i class="fas fa-bell"></i> {{ __('app.auth.reminder_ready') }}</li>
            </ul>
        </div>

        <div class="ypk-login-form">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h2 class="mb-1">{{ __('app.auth.login_title') }}</h2>
                    <p class="mb-0">{{ __('app.auth.login_subtitle') }}</p>
                </div>
                <div class="d-flex align-items-center" style="gap:8px;">
                    <form method="POST" action="{{ route('locale.update', ['locale' => 'id']) }}">
                        @csrf
                        <button type="submit" class="btn btn-sm {{ app()->getLocale() === 'id' ? 'btn-primary' : 'btn-light' }}">ID</button>
                    </form>
                    <form method="POST" action="{{ route('locale.update', ['locale' => 'en']) }}">
                        @csrf
                        <button type="submit" class="btn btn-sm {{ app()->getLocale() === 'en' ? 'btn-primary' : 'btn-light' }}">EN</button>
                    </form>
                </div>
            </div>

            <form id="login-form" action="{{ url()->current() }}" method="post">
                @csrf
                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-user mr-1"></i>
                        {{ __('app.auth.email') }}
                    </label>
                    <input type="text" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" placeholder="{{ __('app.auth.email_placeholder') }}" autocomplete="username">
                    @error('email')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-key mr-1"></i>
                        {{ __('app.auth.password') }}
                    </label>
                    <input
                        type="password"
                        class="form-control
                            @error('password') is-invalid @enderror
                            @if(session('auth_failed')) is-invalid @endif"
                        id="password"
                        name="password"
                        placeholder="{{ __('app.auth.password_placeholder') }}"
                        autocomplete="current-password">
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
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="icheck-primary">
                        <input type="checkbox" id="remember" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                        <label for="remember">
                            {{ __('app.auth.remember') }}
                        </label>
                    </div>
                    <small class="text-muted">{{ __('app.auth.need_help') }}</small>
                </div>
                <button id="submit-form-button" type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-sign-in-alt mr-1"></i>
                    {{ __('app.auth.sign_in') }}
                </button>
            </form>

            <div class="ypk-login-footer">
                {!! str_replace(':app', '<strong>' . e(__('app.app_name')) . '</strong>', e(__('app.auth.ready'))) !!}
            </div>
        </div>
    </div>
</div>

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
<script>
    if (window.ThemeManager) {
        window.ThemeManager.init();
    }
</script>
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
