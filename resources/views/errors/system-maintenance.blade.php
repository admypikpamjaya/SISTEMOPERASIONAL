<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Maintenance</title>
    <style>
        body { margin:0; min-height:100vh; display:flex; align-items:center; justify-content:center; font-family:Arial,sans-serif; background:#f4f7fb; color:#172033; }
        .panel { width:min(560px, calc(100% - 32px)); background:#fff; border:1px solid #dbe4f0; border-radius:8px; padding:28px; box-shadow:0 18px 45px rgba(15,23,42,.12); }
        h1 { margin:0 0 10px; font-size:26px; }
        p { margin:0; line-height:1.6; color:#526173; }
        a { display:inline-flex; margin-top:18px; color:#1d4ed8; font-weight:700; text-decoration:none; }
    </style>
</head>
<body>
    <main class="panel">
        <h1>Sistem Sedang Maintenance</h1>
        <p>{{ $message ?? 'Sistem sedang maintenance berkala. Silakan coba lagi beberapa saat lagi.' }}</p>
        <a href="{{ route('system-management.login') }}">Masuk Sistem Management</a>
    </main>
</body>
</html>
