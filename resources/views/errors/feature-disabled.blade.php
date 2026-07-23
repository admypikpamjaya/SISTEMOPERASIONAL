<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Fitur Dinonaktifkan</title>
    <style>
        body { margin:0; min-height:100vh; display:grid; place-items:center; background:#f8fafc; color:#0f172a; font-family:Arial, sans-serif; }
        .box { width:min(520px, calc(100% - 32px)); background:#fff; border:1px solid #dbeafe; border-radius:8px; padding:26px; box-shadow:0 10px 24px rgba(15,23,42,.08); }
        h1 { margin:0 0 8px; font-size:24px; }
        p { margin:0; color:#64748b; line-height:1.55; }
        a { display:inline-block; margin-top:18px; color:#1d4ed8; font-weight:700; text-decoration:none; }
    </style>
</head>
<body>
    <main class="box">
        <h1>Fitur Sedang Dinonaktifkan</h1>
        <p>{{ $feature['name'] ?? 'Fitur ini' }} saat ini dinonaktifkan oleh Sistem Management untuk maintenance atau pembatasan akses sementara.</p>
        <a href="{{ route('dashboard.index') }}">Kembali ke Dashboard</a>
    </main>
</body>
</html>
