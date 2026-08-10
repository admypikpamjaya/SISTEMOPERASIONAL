<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Lupa Password - {{ __('app.app_name') }}</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}">
    @php($appCssVersion = file_exists(public_path('css/app.css')) ? filemtime(public_path('css/app.css')) : time())
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ $appCssVersion }}">
    <style>
        body { min-height:100vh; margin:0; display:grid; place-items:center; padding:24px; background:#eef3fb; font-family:'Plus Jakarta Sans', Arial, sans-serif; color:#0f172a; }
        .fp-card { width:min(460px,100%); background:#fff; border:1px solid #d7e0ee; border-radius:8px; padding:28px; box-shadow:0 18px 38px rgba(15,23,42,.12); }
        .fp-icon { width:44px; height:44px; border-radius:8px; display:inline-flex; align-items:center; justify-content:center; background:#dbeafe; color:#1d4ed8; margin-bottom:14px; }
        .fp-title { margin:0; font-size:22px; font-weight:800; }
        .fp-sub { margin:8px 0 20px; color:#64748b; font-size:14px; line-height:1.55; }
        .form-control { min-height:44px; border-radius:8px; }
        .btn { min-height:44px; border-radius:8px; font-weight:800; }
        .fp-back { display:inline-flex; margin-top:14px; color:#475569; font-size:13px; }
        .alert { border-radius:8px; }
    </style>
</head>
<body>
    <main class="fp-card">
        <span class="fp-icon"><i class="fas fa-envelope"></i></span>
        <h1 class="fp-title">Lupa Password</h1>
        <p class="fp-sub">Masukkan email akun. Sistem akan mengirim link reset password menggunakan email SMTP default.</p>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" autocomplete="email" required autofocus>
                @error('email')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
            <button type="submit" class="btn btn-primary btn-block">
                <i class="fas fa-paper-plane"></i> Kirim Link Reset
            </button>
        </form>

        <a class="fp-back" href="{{ route('login') }}">Kembali ke login</a>
    </main>
</body>
</html>
