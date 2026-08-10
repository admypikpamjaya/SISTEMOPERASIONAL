<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login Sistem Management</title>
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}">
    <style>
        body { min-height:100vh; margin:0; background:#eef3f8; font-family:Arial,sans-serif; color:#142033; }
        .sm-login { min-height:100vh; display:grid; place-items:center; padding:24px; }
        .sm-card { width:min(960px,100%); display:grid; grid-template-columns:1fr 420px; background:#fff; border:1px solid #d9e3ef; border-radius:8px; box-shadow:0 22px 60px rgba(15,23,42,.16); overflow:hidden; }
        .sm-panel { padding:34px; background:#111827; color:#fff; display:flex; flex-direction:column; justify-content:space-between; min-height:520px; }
        .sm-brand { display:flex; align-items:center; gap:14px; }
        .sm-brand img { width:54px; height:54px; object-fit:contain; background:#fff; border-radius:8px; padding:6px; }
        .sm-eyebrow { color:#93c5fd; font-size:12px; font-weight:800; text-transform:uppercase; letter-spacing:.08em; }
        .sm-title { margin:16px 0 10px; font-size:32px; font-weight:800; line-height:1.1; }
        .sm-copy { margin:0; color:#cbd5e1; max-width:520px; line-height:1.6; }
        .sm-status { display:grid; gap:10px; margin-top:28px; }
        .sm-status span { display:flex; align-items:center; gap:10px; color:#dbeafe; font-size:14px; }
        .sm-status i { color:#60a5fa; width:18px; }
        .sm-form { padding:34px; display:flex; flex-direction:column; justify-content:center; }
        .sm-form h1 { font-size:24px; font-weight:800; margin:0 0 6px; color:#111827; }
        .sm-form p { color:#64748b; margin:0 0 22px; }
        .form-control { min-height:46px; border-radius:8px; border-color:#cbd5e1; }
        .btn-system { min-height:46px; border-radius:8px; background:#1d4ed8; border:0; font-weight:800; }
        .alert { border-radius:8px; }
        .sm-link { margin-top:16px; display:inline-flex; color:#475569; font-size:13px; }
        .sm-reset-box { margin-top:18px; padding-top:18px; border-top:1px solid #e2e8f0; }
        .sm-reset-title { margin:0 0 8px; font-size:13px; font-weight:800; color:#111827; }
        .sm-reset-row { display:grid; grid-template-columns:minmax(0,1fr) auto; gap:8px; align-items:center; }
        .sm-reset-row .form-control { min-height:40px; font-size:13px; }
        .sm-reset-row .btn { min-height:40px; border-radius:8px; font-weight:800; white-space:nowrap; }
        @media (max-width: 900px) { .sm-card { grid-template-columns:1fr; } .sm-panel { min-height:auto; } }
        @media (max-width: 520px) { .sm-reset-row { grid-template-columns:1fr; } }
    </style>
</head>
<body>
<main class="sm-login">
    <section class="sm-card">
        <div class="sm-panel">
            <div>
                <div class="sm-brand">
                    <img src="{{ asset('images/logo_ypik.webp') }}" alt="YPIK">
                    <div>
                        <div class="sm-eyebrow">Privileged Console</div>
                        <strong>SOY YPIK PAM JAYA</strong>
                    </div>
                </div>
                <h2 class="sm-title">Sistem Management</h2>
                <p class="sm-copy">Akses root untuk status sistem, audit keamanan, maintenance, permission role, dan kontrol fitur.</p>
                <div class="sm-status">
                    <span><i class="fas fa-lock"></i> Login khusus tanpa remember session</span>
                    <span><i class="fas fa-history"></i> Aktivitas terekam di audit internal</span>
                    <span><i class="fas fa-user-shield"></i> Role biasa ditolak dari halaman ini</span>
                </div>
            </div>
        </div>

        <form class="sm-form" method="POST" action="{{ route('system-management.login') }}">
            @csrf
            <h1>Masuk Console</h1>
            <p>Gunakan akun Sistem Management yang sudah didaftarkan.</p>

            @if(session('auth_failed'))
                <div class="alert alert-danger">{{ session('auth_failed') }}</div>
            @endif
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" autocomplete="username" required autofocus>
                @error('email')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" autocomplete="current-password" required>
                @error('password')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>

            <button type="submit" class="btn btn-primary btn-system">Masuk Sistem Management</button>

            <div class="sm-reset-box">
                <p class="sm-reset-title">Lupa atau ubah password via email link</p>
                <form method="POST" action="{{ route('system-management.password.email') }}" class="sm-reset-row">
                    @csrf
                    <input
                        type="email"
                        name="email"
                        value="{{ old('email', 'ridodwikurniawan@gmail.com') }}"
                        class="form-control @error('email') is-invalid @enderror"
                        autocomplete="email"
                        required
                    >
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="fas fa-envelope"></i> Kirim Link
                    </button>
                </form>
                @error('email')<span class="text-danger small d-block mt-2">{{ $message }}</span>@enderror
            </div>

            <a class="sm-link" href="{{ route('login') }}">Kembali ke login umum</a>
        </form>
    </section>
</main>
</body>
</html>
