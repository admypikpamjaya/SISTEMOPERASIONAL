@extends('layouts.app')

@section('title', 'Sistem Management')
@section('section_name', 'Sistem Management')

@section('content')
@php
    $page = $page ?? 'overview';
    $pageMeta = [
        'overview' => ['title' => 'Dashboard Sistem', 'subtitle' => 'Ringkasan operasional dan akses cepat Sistem Management.'],
        'status' => ['title' => 'Status Sistem', 'subtitle' => 'Pantau service utama, database, cache, queue, gateway WhatsApp, dan email.'],
        'maintenance' => ['title' => 'Maintenance Web', 'subtitle' => 'Matikan akses semua role tanpa mematikan akses Sistem Management.'],
        'blast-flow' => ['title' => 'Alur Blast', 'subtitle' => 'Lihat posisi campaign WhatsApp dan email dari target sampai final status.'],
        'audit' => ['title' => 'Audit Akses', 'subtitle' => 'Lihat siapa saja yang masuk, API/route yang diakses, browser, IP, dan lokasi.'],
        'users' => ['title' => 'Reset Password', 'subtitle' => 'Reset password seluruh role termasuk superadmin.'],
        'permissions' => ['title' => 'Restrict Role', 'subtitle' => 'Ubah akses halaman per role dari satu panel.'],
        'ai' => ['title' => 'Developer AI', 'subtitle' => 'Kirim instruksi fitur ke AI executor atau buat draft feature flag.'],
        'api-tester' => ['title' => 'Tembak API', 'subtitle' => 'HTTP client internal untuk cek endpoint langsung dari web.'],
        'cms' => ['title' => 'CMS Web', 'subtitle' => 'Ubah brand, banner, layout, dan custom CSS web.'],
        'features' => ['title' => 'Feature Toggle', 'subtitle' => 'Aktifkan atau nonaktifkan fitur tanpa deploy ulang.'],
        'feature-access' => ['title' => 'Akses Fitur', 'subtitle' => 'Aktifkan atau nonaktifkan modul utama web secara terpilih.'],
        'archives' => ['title' => 'Arsip Log Blast', 'subtitle' => 'Log blast yang dihapus superadmin tetap tersimpan di sini.'],
    ];
    $meta = $pageMeta[$page] ?? $pageMeta['overview'];
    $maintenanceOn = (bool) ($maintenance['enabled'] ?? false);
    $maintenanceMessage = (string) ($maintenance['message'] ?? 'Sistem sedang maintenance berkala. Silakan coba lagi beberapa saat lagi.');
    $blastStats = is_array($blastFlows ?? null) ? ($blastFlows['stats'] ?? []) : [];
    $systemDownCount = isset($systems) ? collect($systems)->where('ok', false)->count() : 0;
    $apiResult = $apiTesterResult ?? null;
    $aiResult = $aiExecutionResult ?? null;
    $cmsValue = array_merge([
        'brand_short' => '',
        'sidebar_label' => '',
        'notice_enabled' => false,
        'notice_text' => '',
        'content_width' => 'default',
        'custom_css' => '',
    ], is_array($cms ?? null) ? $cms : []);
    $aiExecutorReady = filled(config('system_management.ai_executor.endpoint'));
    $featureDisableMin = now('Asia/Jakarta')->addMinutes(5)->format('Y-m-d\TH:i');
    $quickLinks = [
        ['label' => 'Status', 'icon' => 'fas fa-server', 'route' => 'system-management.status'],
        ['label' => 'Maintenance', 'icon' => 'fas fa-power-off', 'route' => 'system-management.maintenance'],
        ['label' => 'Alur Blast', 'icon' => 'fas fa-project-diagram', 'route' => 'system-management.blast-flow'],
        ['label' => 'Audit', 'icon' => 'fas fa-user-clock', 'route' => 'system-management.audit'],
        ['label' => 'Password', 'icon' => 'fas fa-key', 'route' => 'system-management.users'],
        ['label' => 'Role', 'icon' => 'fas fa-user-lock', 'route' => 'system-management.permissions'],
        ['label' => 'AI', 'icon' => 'fas fa-robot', 'route' => 'system-management.ai'],
        ['label' => 'API Tester', 'icon' => 'fas fa-bolt', 'route' => 'system-management.api-tester'],
        ['label' => 'CMS', 'icon' => 'fas fa-paint-brush', 'route' => 'system-management.cms'],
        ['label' => 'Fitur', 'icon' => 'fas fa-toggle-on', 'route' => 'system-management.features'],
        ['label' => 'Akses Fitur', 'icon' => 'fas fa-sliders-h', 'route' => 'system-management.feature-access'],
        ['label' => 'Arsip', 'icon' => 'fas fa-archive', 'route' => 'system-management.archives'],
    ];
    $tutorials = [
        'overview' => [
            'title' => 'Tutorial Dashboard Sistem',
            'steps' => [
                'Mulai dari empat ringkasan atas untuk melihat sistem yang perlu dicek, antrean gateway, blast gagal, dan status maintenance.',
                'Gunakan Aksi Cepat untuk pindah ke modul yang ingin dikerjakan tanpa scroll panjang.',
                'Pakai tombol Matikan Web hanya saat maintenance, lalu pakai Nyalakan Web setelah pengecekan selesai.',
            ],
        ],
        'status' => [
            'title' => 'Tutorial Membaca Status Sistem',
            'steps' => [
                'Cari kartu berlabel Perlu Cek karena kartu itu menandakan service yang tidak normal.',
                'Baca detail pada kartu untuk mengetahui penyebab awal, misalnya database down, gateway unreachable, atau queue masih sync.',
                'Jika WhatsApp Gateway bermasalah, cek service gateway dan worker queue sebelum mengirim blast ulang.',
            ],
        ],
        'maintenance' => [
            'title' => 'Tutorial Pengisian Maintenance',
            'steps' => [
                'Pilih Web Normal untuk membuka akses semua role, atau Maintenance Aktif untuk menutup akses semua role selain Sistem Management.',
                'Isi Pesan Maintenance dengan kalimat singkat yang akan dilihat user saat web ditutup.',
                'Klik Simpan Maintenance untuk menyimpan pesan, atau gunakan tombol Matikan Web/Nyalakan Web untuk aksi cepat.',
            ],
        ],
        'blast-flow' => [
            'title' => 'Tutorial Membaca Alur Blast',
            'steps' => [
                'Baca urutan Target, Campaign, Queue, Provider, dan Final untuk mengetahui posisi proses blast.',
                'Perhatikan kolom Failed dan Pending pada campaign WhatsApp atau email yang sedang dicek.',
                'Jika Pending tinggi, cek status gateway, queue worker, dan response provider sebelum retry manual.',
            ],
        ],
        'audit' => [
            'title' => 'Tutorial Membaca Audit Akses',
            'steps' => [
                'Gunakan tabel Audit Akses untuk melihat user, route/API yang dibuka, browser, IP, lokasi, status, dan durasi request.',
                'Gunakan Riwayat Login untuk mencocokkan waktu login dengan aktivitas yang tercatat di audit.',
                'Jika ada akses mencurigakan, catat email user, IP, browser, dan waktu sebelum reset password atau pembatasan role.',
            ],
        ],
        'users' => [
            'title' => 'Tutorial Reset Password',
            'steps' => [
                'Cari user berdasarkan nama, email, dan role pada tabel.',
                'Isi Password Baru minimal 12 karakter, gunakan kombinasi huruf besar, huruf kecil, angka, dan simbol.',
                'Klik Reset pada baris user yang benar, lalu minta user login ulang dengan password baru.',
            ],
        ],
        'permissions' => [
            'title' => 'Tutorial Restrict Role',
            'steps' => [
                'Pilih Role yang ingin diatur. Role Sistem Management tidak bisa dibatasi karena aksesnya root.',
                'Pilih Permission halaman yang ingin diubah, lalu pilih Izinkan atau Blokir.',
                'Klik Simpan Restrict, lalu cek Ringkasan Akses Role untuk memastikan override manual bertambah.',
            ],
        ],
        'ai' => [
            'title' => 'Tutorial Developer AI',
            'steps' => [
                'Untuk AI Action, pilih Plan jika hanya ingin analisa dan rencana, atau Apply jika endpoint AI executor sudah siap menjalankan perubahan.',
                'Isi Target Scope dengan area file atau modul, misalnya blast_whatsapp, routes/web.php, atau app/Http/Controllers/Admin/BlastController.php.',
                'Isi Instruksi Fitur dengan tujuan yang spesifik, lalu klik Jalankan AI. Untuk draft fitur, isi Modul dan Fungsi Sistem lalu klik Buat Draft.',
            ],
        ],
        'api-tester' => [
            'title' => 'Tutorial Tembak API',
            'steps' => [
                'Pilih Method sesuai kebutuhan endpoint, misalnya GET untuk baca data atau POST untuk kirim data.',
                'Isi URL lengkap beserta protokol http atau https. Isi Headers JSON jika endpoint butuh token atau Accept application/json.',
                'Pilih Body Type sesuai payload. Gunakan JSON untuk API modern, form untuk application/x-www-form-urlencoded, raw untuk payload bebas.',
                'Atur Timeout Detik lebih besar untuk endpoint lambat, lalu klik Kirim Request dan baca Response Body serta Response Headers.',
            ],
        ],
        'cms' => [
            'title' => 'Tutorial CMS Web',
            'steps' => [
                'Isi Brand Pendek atau Label Sidebar jika ingin mengganti teks brand di sidebar.',
                'Pilih Lebar Konten Default, Wide, atau Compact sesuai kebutuhan tampilan halaman.',
                'Centang Banner Aktif dan isi Teks Banner jika ingin menampilkan pengumuman global di atas konten.',
                'Isi Custom CSS hanya untuk penyesuaian kecil, lalu klik Simpan CMS.',
            ],
        ],
        'features' => [
            'title' => 'Tutorial Feature Toggle',
            'steps' => [
                'Cari fitur berdasarkan nama dan key agar tidak salah menonaktifkan modul untuk seluruh role.',
                'Saat menonaktifkan, isi alasan maintenance dan waktu sampai kapan fitur ditutup.',
                'Jika waktu nonaktif sudah lewat, fitur otomatis tayang kembali dan Sistem Management akan mendapat konfirmasi untuk lanjut maintenance atau tetap tayangkan fitur.',
            ],
        ],
        'feature-access' => [
            'title' => 'Tutorial Akses Fitur',
            'steps' => [
                'Cari modul utama yang ingin dibatasi, misalnya Blasting WhatsApp & Email, Finance, atau Asset Management.',
                'Untuk menonaktifkan modul, klik Atur Maintenance agar alasan dan batas waktu wajib tercatat di Feature Toggle.',
                'Jika fitur sudah nonaktif, klik Tayangkan untuk membuka kembali fitur. Fitur Sistem Management dikunci agar akses pemulihan tetap tersedia.',
            ],
        ],
        'archives' => [
            'title' => 'Tutorial Membaca Arsip Log',
            'steps' => [
                'Gunakan kolom Asal dan Target untuk mengetahui log blast mana yang pernah dihapus.',
                'Baca Status, Provider Status, dan Error/Response untuk memahami penyebab gagal atau pending.',
                'Gunakan Dihapus Oleh dan Diarsipkan untuk audit internal saat log di halaman superadmin sudah terhapus.',
            ],
        ],
    ];
    $activeTutorial = $tutorials[$page] ?? $tutorials['overview'];
@endphp

<style>
    .smx { display:flex; flex-direction:column; gap:16px; color:var(--app-text); }
    .smx-panel, .smx-card { background:var(--app-surface); border:1px solid var(--app-border); border-radius:8px; box-shadow:var(--app-shadow); }
    .smx-hero { display:grid; grid-template-columns:minmax(0,1fr) auto; gap:16px; align-items:center; padding:18px; background:#111827; border-radius:8px; color:#fff; box-shadow:var(--app-shadow); }
    .smx-title { margin:0; font-size:24px; font-weight:800; letter-spacing:0; }
    .smx-sub { margin:5px 0 0; color:rgba(255,255,255,.75); }
    .smx-pill { display:inline-flex; align-items:center; gap:7px; padding:8px 11px; border-radius:999px; background:rgba(255,255,255,.12); color:#fff; font-weight:800; white-space:nowrap; }
    .smx-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:12px; }
    .smx-two { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
    .smx-actions { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:10px; }
    .smx-action, .smx-btn { display:inline-flex; align-items:center; justify-content:center; gap:8px; border:0; border-radius:8px; min-height:40px; padding:10px 12px; font-weight:800; line-height:1.1; }
    .smx-action { background:var(--app-surface-soft); color:var(--app-text); border:1px solid var(--app-border); text-decoration:none; }
    .smx-action.active, .smx-action:hover { color:var(--app-text); text-decoration:none; border-color:#2563eb; }
    .smx-btn.primary { background:#1d4ed8; color:#fff; }
    .smx-btn.danger { background:#dc2626; color:#fff; }
    .smx-btn.success { background:#16a34a; color:#fff; }
    .smx-btn.soft { background:#e2e8f0; color:#0f172a; }
    .smx-btn.block { width:100%; }
    .smx-panel-header { padding:14px 16px; border-bottom:1px solid var(--app-border); display:flex; justify-content:space-between; gap:12px; align-items:center; }
    .smx-panel-title { margin:0; font-weight:800; font-size:16px; letter-spacing:0; }
    .smx-panel-body { padding:16px; }
    .smx-muted { color:var(--app-text-muted); }
    .smx-guide { border-left:4px solid #2563eb; }
    .smx-guide .smx-panel-header { background:var(--app-surface-soft); }
    .smx-guide-list { margin:0; padding-left:20px; color:var(--app-text); }
    .smx-guide-list li { margin:0 0 8px; line-height:1.5; }
    .smx-guide-list li:last-child { margin-bottom:0; }
    .smx-metric { padding:14px; }
    .smx-metric span { display:block; font-size:11px; text-transform:uppercase; color:var(--app-text-muted); font-weight:800; }
    .smx-metric strong { display:block; margin-top:5px; font-size:22px; }
    .smx-system { border-left:4px solid #16a34a; min-height:124px; padding:14px; }
    .smx-system.down { border-left-color:#dc2626; }
    .smx-system h3 { margin:0 0 8px; font-size:14px; font-weight:800; }
    .smx-system .state { display:inline-flex; padding:4px 8px; border-radius:999px; font-size:11px; font-weight:800; background:#f1f5f9; color:#334155; }
    .smx-system p { margin:10px 0 0; color:var(--app-text-muted); font-size:12px; line-height:1.45; }
    .smx-form-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:10px; align-items:end; }
    .smx-form-grid.three { grid-template-columns:1fr 1fr auto; }
    .smx-api-url { grid-column:span 2; }
    .smx-label { font-size:12px; font-weight:800; color:var(--app-text-muted); margin-bottom:5px; }
    .smx-table-wrap { overflow:auto; }
    .smx-table { width:100%; margin:0; font-size:13px; }
    .smx-table th { white-space:nowrap; color:var(--app-text-muted); font-size:11px; text-transform:uppercase; border-top:0; }
    .smx-table td { vertical-align:middle; }
    .smx-codebox { display:block; margin:0; max-height:340px; overflow:auto; padding:12px; border-radius:8px; background:#0f172a; color:#e2e8f0; font-size:12px; white-space:pre-wrap; word-break:break-word; }
    .smx-result { border:1px solid var(--app-border); border-radius:8px; overflow:hidden; }
    .smx-result-head { display:flex; justify-content:space-between; gap:10px; padding:10px 12px; background:var(--app-surface-soft); border-bottom:1px solid var(--app-border); font-weight:800; }
    .smx-result.ok .smx-result-head { color:#15803d; }
    .smx-result.fail .smx-result-head { color:#b91c1c; }
    .smx-flow { display:grid; grid-template-columns:repeat(5,minmax(0,1fr)); gap:8px; }
    .smx-step { padding:10px; border:1px solid var(--app-border); border-radius:8px; background:var(--app-surface-soft); min-height:76px; }
    .smx-step strong { display:block; font-size:12px; }
    .smx-step span { display:block; margin-top:6px; color:var(--app-text-muted); font-size:12px; }
    .smx-radio-row { display:flex; gap:12px; flex-wrap:wrap; }
    .smx-radio { display:flex; align-items:center; gap:7px; padding:8px 10px; border:1px solid var(--app-border); border-radius:8px; background:var(--app-surface-soft); font-weight:700; }
    .smx-feature-list { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:12px; }
    .smx-feature-item { padding:14px; border:1px solid var(--app-border); border-radius:8px; background:var(--app-surface); display:grid; grid-template-columns:minmax(0,1fr) auto; gap:12px; align-items:center; }
    .smx-feature-item h3 { margin:0; font-size:14px; font-weight:800; }
    .smx-feature-item p { margin:5px 0 0; font-size:12px; color:var(--app-text-muted); line-height:1.45; }
    .smx-feature-badge { display:inline-flex; width:max-content; padding:4px 8px; border-radius:999px; font-size:11px; font-weight:800; margin-top:8px; }
    .smx-feature-badge.on { background:#dcfce7; color:#15803d; }
    .smx-feature-badge.off { background:#fee2e2; color:#b91c1c; }
    .smx-feature-badge.locked { background:#e0f2fe; color:#0369a1; }
    .smx-feature-toggle-list { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:12px; }
    .smx-feature-toggle-item { border:1px solid var(--app-border); border-radius:8px; background:var(--app-surface); padding:14px; display:flex; flex-direction:column; gap:12px; }
    .smx-feature-toggle-head { display:flex; justify-content:space-between; gap:12px; align-items:flex-start; }
    .smx-feature-toggle-title { margin:0; font-size:15px; font-weight:800; }
    .smx-feature-toggle-desc { margin:6px 0 0; color:var(--app-text-muted); font-size:12px; line-height:1.45; }
    .smx-feature-toggle-meta { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:8px; }
    .smx-mini-box { border:1px solid var(--app-border); border-radius:8px; background:var(--app-surface-soft); padding:9px 10px; min-width:0; }
    .smx-mini-box span { display:block; color:var(--app-text-muted); font-size:10px; font-weight:800; text-transform:uppercase; }
    .smx-mini-box strong { display:block; margin-top:3px; color:var(--app-text); font-size:12px; overflow-wrap:anywhere; }
    .smx-toggle-form { border-top:1px solid var(--app-border); padding-top:12px; }
    .smx-toggle-form-grid { display:grid; grid-template-columns:minmax(0,1fr) minmax(190px,.5fr) auto; gap:10px; align-items:end; }
    .smx-expired-list { display:grid; gap:10px; }
    .smx-expired-item { border:1px solid #fcd34d; border-radius:8px; background:#fffbeb; padding:12px; color:#78350f; }
    .smx-expired-head { display:flex; justify-content:space-between; gap:10px; align-items:flex-start; margin-bottom:10px; }
    .smx-expired-title { margin:0; font-size:14px; font-weight:800; color:#78350f; }
    .smx-expired-desc { margin:4px 0 0; font-size:12px; line-height:1.45; }
    .smx-expired-actions { display:grid; grid-template-columns:minmax(0,1fr) auto; gap:10px; align-items:end; }
    .smx-expired-ack { display:flex; align-items:end; justify-content:flex-end; }
    @media (max-width:1200px){ .smx-grid,.smx-actions{grid-template-columns:repeat(2,1fr);} .smx-two,.smx-feature-list{grid-template-columns:1fr;} }
    @media (max-width:1200px){ .smx-feature-toggle-list{grid-template-columns:1fr;} .smx-toggle-form-grid,.smx-expired-actions{grid-template-columns:1fr;} .smx-expired-ack{justify-content:flex-start;} }
    @media (max-width:720px){ .smx-hero,.smx-grid,.smx-actions,.smx-form-grid,.smx-form-grid.three,.smx-flow,.smx-feature-item,.smx-feature-toggle-meta{grid-template-columns:1fr;} .smx-api-url{grid-column:auto;} .smx-hero{align-items:flex-start;} .smx-pill{white-space:normal;} }
</style>

<div class="smx">
    <header class="smx-hero">
        <div>
            <h1 class="smx-title">{{ $meta['title'] }}</h1>
            <p class="smx-sub">{{ $meta['subtitle'] }}</p>
        </div>
        <span class="smx-pill"><i class="fas fa-shield-alt"></i> Sistem Management</span>
    </header>

    @if(session('success'))
        <div class="alert alert-success mb-0">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger mb-0">{{ $errors->first() }}</div>
    @endif

    <section class="smx-panel smx-guide">
        <div class="smx-panel-header">
            <h2 class="smx-panel-title"><i class="fas fa-info-circle"></i> {{ $activeTutorial['title'] }}</h2>
            <span class="smx-muted">Panduan pengisian</span>
        </div>
        <div class="smx-panel-body">
            <ol class="smx-guide-list">
                @foreach($activeTutorial['steps'] as $step)
                    <li>{{ $step }}</li>
                @endforeach
            </ol>
        </div>
    </section>

    @if($page === 'overview')
        <section class="smx-grid">
            <div class="smx-card smx-metric"><span>Sistem Perlu Cek</span><strong>{{ $systemDownCount }}</strong></div>
            <div class="smx-card smx-metric"><span>Gateway Pending</span><strong>{{ $blastStats['provider_pending'] ?? 0 }}</strong></div>
            <div class="smx-card smx-metric"><span>Blast Failed</span><strong>{{ $blastStats['failed_total'] ?? 0 }}</strong></div>
            <div class="smx-card smx-metric"><span>Maintenance</span><strong>{{ $maintenanceOn ? 'ON' : 'OFF' }}</strong></div>
        </section>

        <section class="smx-panel">
            <div class="smx-panel-header">
                <h2 class="smx-panel-title">Aksi Cepat</h2>
                <span class="smx-muted">{{ route('system-management.login') }}</span>
            </div>
            <div class="smx-panel-body">
                <div class="smx-actions">
                    @foreach($quickLinks as $link)
                        <a class="smx-action {{ request()->routeIs($link['route']) ? 'active' : '' }}" href="{{ route($link['route']) }}">
                            <i class="{{ $link['icon'] }}"></i> {{ $link['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="smx-panel">
            <div class="smx-panel-header"><h2 class="smx-panel-title">Maintenance Global</h2><span class="smx-muted">{{ $maintenanceOn ? 'Aktif' : 'Normal' }}</span></div>
            <div class="smx-panel-body">
                <div class="smx-two">
                    <form method="POST" action="{{ route('system-management.maintenance.update') }}">
                        @csrf
                        <input type="hidden" name="enabled" value="1">
                        <input type="hidden" name="message" value="{{ $maintenanceMessage }}">
                        <button class="smx-btn danger block" type="submit"><i class="fas fa-power-off"></i> Matikan Web</button>
                    </form>
                    <form method="POST" action="{{ route('system-management.maintenance.update') }}">
                        @csrf
                        <input type="hidden" name="enabled" value="0">
                        <input type="hidden" name="message" value="{{ $maintenanceMessage }}">
                        <button class="smx-btn success block" type="submit"><i class="fas fa-play"></i> Nyalakan Web</button>
                    </form>
                </div>
            </div>
        </section>
    @endif

    @if($page === 'overview' || $page === 'status')
        <section class="smx-panel">
            <div class="smx-panel-header">
                <h2 class="smx-panel-title">Status Sistem</h2>
                <span class="smx-muted">{{ now('Asia/Jakarta')->format('d/m/Y H:i:s') }} WIB</span>
            </div>
            <div class="smx-panel-body">
                <div class="smx-grid">
                    @foreach($systems ?? [] as $system)
                        <div class="smx-card smx-system {{ $system['ok'] ? 'ok' : 'down' }}">
                            <h3>{{ $system['name'] }}</h3>
                            <span class="state">{{ $system['ok'] ? 'BERJALAN' : 'PERLU CEK' }} - {{ $system['state'] }}</span>
                            <p>{{ $system['detail'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if($page === 'maintenance')
        <section class="smx-panel">
            <div class="smx-panel-header"><h2 class="smx-panel-title">Maintenance Web</h2><span class="smx-muted">{{ $maintenanceOn ? 'Aktif' : 'Normal' }}</span></div>
            <div class="smx-panel-body">
                <div class="smx-two mb-3">
                    <form method="POST" action="{{ route('system-management.maintenance.update') }}">
                        @csrf
                        <input type="hidden" name="enabled" value="1">
                        <input type="hidden" name="message" value="{{ $maintenanceMessage }}">
                        <button class="smx-btn danger block" type="submit"><i class="fas fa-power-off"></i> Matikan Web</button>
                    </form>
                    <form method="POST" action="{{ route('system-management.maintenance.update') }}">
                        @csrf
                        <input type="hidden" name="enabled" value="0">
                        <input type="hidden" name="message" value="{{ $maintenanceMessage }}">
                        <button class="smx-btn success block" type="submit"><i class="fas fa-play"></i> Nyalakan Web</button>
                    </form>
                </div>
                <form method="POST" action="{{ route('system-management.maintenance.update') }}">
                    @csrf
                    <div class="smx-radio-row mb-3">
                        <label class="smx-radio"><input type="radio" name="enabled" value="0" @checked(!$maintenanceOn)> Web Normal</label>
                        <label class="smx-radio"><input type="radio" name="enabled" value="1" @checked($maintenanceOn)> Maintenance Aktif</label>
                    </div>
                    <div class="form-group">
                        <label class="smx-label">Pesan Maintenance</label>
                        <textarea name="message" class="form-control" rows="3">{{ old('message', $maintenanceMessage) }}</textarea>
                    </div>
                    <button class="smx-btn primary" type="submit"><i class="fas fa-save"></i> Simpan Maintenance</button>
                </form>
            </div>
        </section>
    @endif

    @if($page === 'blast-flow')
        <section class="smx-panel">
            <div class="smx-panel-header">
                <h2 class="smx-panel-title">Alur Blasting WhatsApp & Email</h2>
                <span class="smx-muted">Gateway pending: {{ $blastStats['provider_pending'] ?? 0 }}</span>
            </div>
            <div class="smx-panel-body">
                <div class="smx-flow mb-3">
                    <div class="smx-step"><strong>1. Target</strong><span>Input recipient dibuat.</span></div>
                    <div class="smx-step"><strong>2. Campaign</strong><span>Data masuk blast_messages.</span></div>
                    <div class="smx-step"><strong>3. Queue</strong><span>Log target PENDING.</span></div>
                    <div class="smx-step"><strong>4. Provider</strong><span>WA gateway atau SMTP.</span></div>
                    <div class="smx-step"><strong>5. Final</strong><span>SENT, FAILED, PENDING.</span></div>
                </div>
                <div class="smx-two">
                    @foreach(['whatsapp' => 'WhatsApp', 'email' => 'Email'] as $key => $label)
                        <div class="smx-table-wrap">
                            <table class="table smx-table">
                                <thead><tr><th>{{ $label }} Campaign</th><th>Status</th><th>Total</th><th>Sent</th><th>Failed</th><th>Pending</th></tr></thead>
                                <tbody>
                                    @forelse($blastFlows[$key] ?? [] as $campaign)
                                        <tr>
                                            <td><code>{{ \Illuminate\Support\Str::limit($campaign->id, 10) }}</code><br><span class="smx-muted">{{ $campaign->created_at?->timezone('Asia/Jakarta')->format('d/m/Y H:i') }}</span></td>
                                            <td>{{ $campaign->campaign_status ?? '-' }}</td>
                                            <td>{{ $campaign->total_logs ?? 0 }}</td>
                                            <td>{{ $campaign->sent_logs ?? 0 }}</td>
                                            <td>{{ $campaign->failed_logs ?? 0 }}</td>
                                            <td>{{ $campaign->pending_logs ?? 0 }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="6" class="text-center smx-muted">Belum ada campaign.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if($page === 'audit')
        <div class="smx-two">
            <section class="smx-panel">
                <div class="smx-panel-header"><h2 class="smx-panel-title">Audit Akses Web/API</h2></div>
                <div class="smx-table-wrap">
                    <table class="table smx-table">
                        <thead><tr><th>User</th><th>Route/API</th><th>Browser</th><th>IP & Lokasi</th><th>Status</th><th>Waktu</th></tr></thead>
                        <tbody>
                            @forelse($accessLogs ?? [] as $log)
                                <tr>
                                    <td>{{ $log->user?->name ?? 'Guest' }}<br><span class="smx-muted">{{ $log->user?->role ?? '-' }}</span></td>
                                    <td><code>{{ $log->method }} {{ $log->path }}</code><br><span class="smx-muted">{{ $log->route_name ?? '-' }}</span></td>
                                    <td>{{ $log->browser ?? '-' }}<br><span class="smx-muted">{{ $log->platform ?? '-' }} / {{ $log->device ?? '-' }}</span></td>
                                    <td>{{ $log->ip_address ?? '-' }}<br><span class="smx-muted">{{ $log->location_summary ?? '-' }}</span></td>
                                    <td>{{ $log->status_code ?? '-' }} / {{ $log->duration_ms ?? 0 }}ms</td>
                                    <td>{{ $log->occurred_at?->timezone('Asia/Jakarta')->format('d/m/Y H:i:s') ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center smx-muted">Audit belum tersedia.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="smx-panel">
                <div class="smx-panel-header"><h2 class="smx-panel-title">Riwayat Login</h2></div>
                <div class="smx-table-wrap">
                    <table class="table smx-table">
                        <thead><tr><th>User</th><th>Browser</th><th>IP & Lokasi</th><th>Login</th></tr></thead>
                        <tbody>
                            @forelse($loginHistories ?? [] as $history)
                                <tr>
                                    <td>{{ $history->user?->name ?? '-' }}<br><span class="smx-muted">{{ $history->user?->email ?? '-' }}</span></td>
                                    <td>{{ $history->browser ?? $history->user_agent_info }}<br><span class="smx-muted">{{ $history->platform ?? '-' }} / {{ $history->device ?? '-' }}</span></td>
                                    <td>{{ $history->ip_address ?? '-' }}<br><span class="smx-muted">{{ $history->location_summary ?? '-' }}</span></td>
                                    <td>{{ $history->logged_in_at?->timezone('Asia/Jakarta')->format('d/m/Y H:i:s') ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center smx-muted">Belum ada login.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    @endif

    @if($page === 'users')
        <section class="smx-panel">
            <div class="smx-panel-header"><h2 class="smx-panel-title">Reset Password Semua Role</h2></div>
            <div class="smx-table-wrap">
                <table class="table smx-table">
                    <thead><tr><th>User</th><th>Email</th><th>Role</th><th>Password Baru</th><th>Aksi</th></tr></thead>
                    <tbody>
                        @foreach($users ?? [] as $user)
                            @php($isSystemManagementUser = $user->role === \App\Enums\User\UserRole::SYSTEM_MANAGEMENT->value)
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->role }}</td>
                                <td>
                                    <form id="password-{{ $user->id }}" method="POST" action="{{ route('system-management.users.password', $user) }}">
                                        @csrf
                                        @if($isSystemManagementUser)
                                            <span class="smx-muted">Wajib via email link ke akun Sistem Management.</span>
                                        @else
                                            <input type="password" name="password" class="form-control form-control-sm" placeholder="Minimal 12 karakter" required minlength="12">
                                        @endif
                                    </form>
                                </td>
                                <td>
                                    <button class="smx-btn primary" type="submit" form="password-{{ $user->id }}">
                                        <i class="fas {{ $isSystemManagementUser ? 'fa-envelope' : 'fa-key' }}"></i>
                                        {{ $isSystemManagementUser ? 'Kirim Link' : 'Reset' }}
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @endif

    @if($page === 'permissions')
        <section class="smx-panel">
            <div class="smx-panel-header"><h2 class="smx-panel-title">Restrict Halaman Per Role</h2></div>
            <div class="smx-panel-body">
                <form method="POST" action="{{ route('system-management.permissions.update') }}" class="smx-form-grid">
                    @csrf
                    <div>
                        <label class="smx-label">Role</label>
                        <select name="role" class="form-control" required>
                            @foreach($roles ?? [] as $role)
                                @if($role->value !== \App\Enums\User\UserRole::SYSTEM_MANAGEMENT->value)
                                    <option value="{{ $role->value }}">{{ $role->value }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="smx-label">Permission</label>
                        <select name="permission" class="form-control" required>
                            @foreach($permissions ?? [] as $permission)
                                <option value="{{ $permission->value }}">{{ $permission->value }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="smx-label">Akses</label>
                        <select name="allowed" class="form-control">
                            <option value="1">Izinkan</option>
                            <option value="0">Blokir</option>
                        </select>
                    </div>
                    <button class="smx-btn primary" type="submit"><i class="fas fa-save"></i> Simpan Restrict</button>
                </form>
            </div>
        </section>

        <section class="smx-panel">
            <div class="smx-panel-header"><h2 class="smx-panel-title">Ringkasan Akses Role</h2></div>
            <div class="smx-table-wrap">
                <table class="table smx-table">
                    <thead><tr><th>Role</th><th>Akses Aktif</th><th>Override Manual</th></tr></thead>
                    <tbody>
                        @foreach($permissionMatrix ?? [] as $roleName => $access)
                            <tr>
                                <td>{{ $roleName }}</td>
                                <td>{{ count($access['allowed'] ?? []) }}</td>
                                <td>{{ collect($access['overrides'] ?? [])->count() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @endif

    @if($page === 'ai')
        <div class="smx-two">
            <section class="smx-panel">
                <div class="smx-panel-header"><h2 class="smx-panel-title">AI Action</h2><span class="smx-muted">{{ $aiExecutorReady ? 'Tersambung' : 'Belum tersambung' }}</span></div>
                <div class="smx-panel-body">
                    <form method="POST" action="{{ route('system-management.ai.execute') }}">
                        @csrf
                        <div class="smx-form-grid three mb-3">
                            <div>
                                <label class="smx-label">Mode</label>
                                <select name="mode" class="form-control" required>
                                    <option value="plan" @selected(old('mode', 'plan') === 'plan')>Plan</option>
                                    <option value="apply" @selected(old('mode') === 'apply')>Apply</option>
                                </select>
                            </div>
                            <div>
                                <label class="smx-label">Target Scope</label>
                                <input name="target_scope" class="form-control" value="{{ old('target_scope', 'Laravel app') }}" required>
                            </div>
                            <button class="smx-btn primary" type="submit"><i class="fas fa-paper-plane"></i> Jalankan AI</button>
                        </div>
                        <div class="form-group mb-0">
                            <label class="smx-label">Instruksi Fitur</label>
                            <textarea name="instruction" class="form-control" rows="4" required>{{ old('instruction') }}</textarea>
                        </div>
                    </form>
                    @if($aiResult)
                        <div class="smx-result {{ ($aiResult['ok'] ?? false) ? 'ok mt-3' : 'fail mt-3' }}">
                            <div class="smx-result-head"><span>Status: {{ $aiResult['status'] ?? '-' }}</span><span>{{ ($aiResult['ok'] ?? false) ? 'OK' : 'ERROR' }}</span></div>
                            <pre class="smx-codebox">{{ $aiResult['body'] ?? '' }}</pre>
                        </div>
                    @endif
                </div>
            </section>

            <section class="smx-panel">
                <div class="smx-panel-header"><h2 class="smx-panel-title">Draft Fitur AI</h2></div>
                <div class="smx-panel-body">
                    @if($aiDraft ?? null)
                        <div class="alert alert-info">Draft AI siap: <strong>{{ $aiDraft['name'] }}</strong></div>
                    @endif
                    <form method="POST" action="{{ route('system-management.ai.feature-draft') }}" class="mb-3">
                        @csrf
                        <div class="form-group"><label class="smx-label">Modul</label><input name="module" class="form-control" placeholder="blast_whatsapp" required></div>
                        <div class="form-group"><label class="smx-label">Fungsi Sistem</label><textarea name="goal" class="form-control" rows="3" required></textarea></div>
                        <button class="smx-btn soft" type="submit"><i class="fas fa-magic"></i> Buat Draft</button>
                    </form>
                    <form method="POST" action="{{ route('system-management.features.store') }}">
                        @csrf
                        <div class="form-group"><label class="smx-label">Key</label><input name="key" class="form-control" value="{{ $aiDraft['key'] ?? '' }}" required></div>
                        <div class="form-group"><label class="smx-label">Nama</label><input name="name" class="form-control" value="{{ $aiDraft['name'] ?? '' }}" required></div>
                        <div class="form-group"><label class="smx-label">Deskripsi</label><textarea name="description" class="form-control" rows="2">{{ $aiDraft['description'] ?? '' }}</textarea></div>
                        <div class="form-group"><label class="smx-label">AI Prompt</label><textarea name="ai_prompt" class="form-control" rows="3">{{ $aiDraft['ai_prompt'] ?? '' }}</textarea></div>
                        <div class="form-group"><label class="smx-label">Catatan Rollout</label><textarea name="rollout_notes" class="form-control" rows="2">{{ $aiDraft['rollout_notes'] ?? '' }}</textarea></div>
                        <button class="smx-btn primary" type="submit"><i class="fas fa-save"></i> Simpan Draft Fitur</button>
                    </form>
                </div>
            </section>
        </div>
    @endif

    @if($page === 'api-tester')
        <section class="smx-panel">
            <div class="smx-panel-header"><h2 class="smx-panel-title">Tembak API</h2><span class="smx-muted">HTTP client internal</span></div>
            <div class="smx-panel-body">
                <form method="POST" action="{{ route('system-management.api-tester.send') }}">
                    @csrf
                    <div class="smx-form-grid mb-3">
                        <div>
                            <label class="smx-label">Method</label>
                            <select name="method" class="form-control" required>
                                @foreach(['GET','POST','PUT','PATCH','DELETE','HEAD','OPTIONS'] as $method)
                                    <option value="{{ $method }}" @selected(old('method', 'GET') === $method)>{{ $method }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="smx-api-url"><label class="smx-label">URL</label><input name="url" type="url" class="form-control" value="{{ old('url') }}" placeholder="https://example.com/api" required></div>
                        <div><label class="smx-label">Timeout Detik</label><input name="timeout" type="number" min="1" max="60" class="form-control" value="{{ old('timeout', 15) }}"></div>
                    </div>
                    <div class="smx-two">
                        <div class="form-group"><label class="smx-label">Headers JSON</label><textarea name="headers_json" class="form-control" rows="5" placeholder='{"Accept":"application/json"}'>{{ old('headers_json') }}</textarea></div>
                        <div>
                            <div class="form-group">
                                <label class="smx-label">Body Type</label>
                                <select name="body_type" class="form-control">
                                    @foreach(['none' => 'None', 'json' => 'JSON', 'form' => 'Form URL Encoded', 'raw' => 'Raw'] as $value => $label)
                                        <option value="{{ $value }}" @selected(old('body_type', 'none') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group mb-0"><label class="smx-label">Body</label><textarea name="body" class="form-control" rows="5">{{ old('body') }}</textarea></div>
                        </div>
                    </div>
                    <button class="smx-btn primary mt-3" type="submit"><i class="fas fa-bolt"></i> Kirim Request</button>
                </form>
                @if($apiResult)
                    <div class="smx-result {{ ($apiResult['ok'] ?? false) ? 'ok mt-3' : 'fail mt-3' }}">
                        <div class="smx-result-head"><span>{{ $apiResult['method'] ?? '-' }} {{ $apiResult['url'] ?? '-' }}</span><span>{{ $apiResult['status'] ?? '-' }} / {{ $apiResult['duration_ms'] ?? 0 }}ms</span></div>
                        <div class="smx-two p-3">
                            <div><label class="smx-label">Response Body</label><pre class="smx-codebox">{{ $apiResult['body'] ?? '' }}</pre></div>
                            <div><label class="smx-label">Response Headers</label><pre class="smx-codebox">{{ json_encode($apiResult['headers'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre></div>
                        </div>
                    </div>
                @endif
            </div>
        </section>
    @endif

    @if($page === 'cms')
        <section class="smx-panel">
            <div class="smx-panel-header"><h2 class="smx-panel-title">CMS Tampilan Web</h2><span class="smx-muted">Brand, layout, banner, CSS</span></div>
            <div class="smx-panel-body">
                <form method="POST" action="{{ route('system-management.cms.update') }}">
                    @csrf
                    <div class="smx-form-grid mb-3">
                        <div><label class="smx-label">Brand Pendek</label><input name="brand_short" class="form-control" value="{{ old('brand_short', $cmsValue['brand_short']) }}"></div>
                        <div><label class="smx-label">Label Sidebar</label><input name="sidebar_label" class="form-control" value="{{ old('sidebar_label', $cmsValue['sidebar_label']) }}"></div>
                        <div>
                            <label class="smx-label">Lebar Konten</label>
                            <select name="content_width" class="form-control">
                                @foreach(['default' => 'Default', 'wide' => 'Wide', 'compact' => 'Compact'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('content_width', $cmsValue['content_width']) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <label class="smx-radio mb-0"><input type="checkbox" name="notice_enabled" value="1" @checked(old('notice_enabled', $cmsValue['notice_enabled']))> Banner Aktif</label>
                    </div>
                    <div class="smx-two">
                        <div class="form-group"><label class="smx-label">Teks Banner</label><textarea name="notice_text" class="form-control" rows="4">{{ old('notice_text', $cmsValue['notice_text']) }}</textarea></div>
                        <div class="form-group"><label class="smx-label">Custom CSS</label><textarea name="custom_css" class="form-control" rows="4">{{ old('custom_css', $cmsValue['custom_css']) }}</textarea></div>
                    </div>
                    <button class="smx-btn primary" type="submit"><i class="fas fa-save"></i> Simpan CMS</button>
                </form>
            </div>
        </section>
    @endif

    @if($page === 'features')
        @if(!empty($expiredFeatureNotices))
            <section class="smx-panel">
                <div class="smx-panel-header">
                    <h2 class="smx-panel-title"><i class="fas fa-clock"></i> Konfirmasi Maintenance Fitur Selesai</h2>
                    <span class="smx-muted">Fitur sudah otomatis tayang kembali</span>
                </div>
                <div class="smx-panel-body">
                    <div class="smx-expired-list">
                        @foreach($expiredFeatureNotices as $notice)
                            <div class="smx-expired-item">
                                <div class="smx-expired-head">
                                    <div>
                                        <h3 class="smx-expired-title">{{ $notice['name'] ?? $notice['key'] }}</h3>
                                        <p class="smx-expired-desc">
                                            Masa nonaktif selesai pada {{ $notice['disabled_until_label'] ?? '-' }} dan fitur sudah otomatis ditayangkan kembali.
                                            @if(!empty($notice['reason']))
                                                Alasan sebelumnya: {{ $notice['reason'] }}.
                                            @endif
                                        </p>
                                    </div>
                                    <span class="smx-feature-badge on">Sudah Tayang</span>
                                </div>
                                <div class="smx-expired-actions">
                                    <form method="POST" action="{{ route('system-management.features.expired-resolution') }}">
                                        @csrf
                                        <input type="hidden" name="feature_key" value="{{ $notice['key'] }}">
                                        <input type="hidden" name="action" value="continue">
                                        <div class="smx-toggle-form-grid">
                                            <div>
                                                <label class="smx-label">Alasan lanjut maintenance</label>
                                                <input name="disable_reason" class="form-control" value="{{ old('disable_reason', 'Maintenance fitur dilanjutkan.') }}" maxlength="500">
                                            </div>
                                            <div>
                                                <label class="smx-label">Nonaktif sampai</label>
                                                <input type="datetime-local" name="disabled_until" class="form-control" min="{{ $featureDisableMin }}" required>
                                            </div>
                                            <button class="smx-btn danger" type="submit">Lanjut Maintenance</button>
                                        </div>
                                    </form>
                                    <form method="POST" action="{{ route('system-management.features.expired-resolution') }}" class="smx-expired-ack">
                                        @csrf
                                        <input type="hidden" name="feature_key" value="{{ $notice['key'] }}">
                                        <input type="hidden" name="action" value="show">
                                        <button class="smx-btn success" type="submit">Tetap Tayangkan</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        <section class="smx-panel">
            <div class="smx-panel-header">
                <h2 class="smx-panel-title">Feature Toggle Semua Role</h2>
                <span class="smx-muted">Berlaku untuk seluruh role selain Sistem Management</span>
            </div>
            <div class="smx-panel-body">
                <div class="smx-feature-toggle-list">
                    @forelse($availableFeatures ?? [] as $feature)
                        @php
                            $isEnabled = (bool) ($feature['is_enabled'] ?? true);
                            $isLocked = (bool) ($feature['locked'] ?? false);
                            $routePatterns = implode(', ', (array) ($feature['route_patterns'] ?? []));
                        @endphp
                        <div class="smx-feature-toggle-item">
                            <div class="smx-feature-toggle-head">
                                <div>
                                    <h3 class="smx-feature-toggle-title">{{ $feature['name'] ?? $feature['key'] }}</h3>
                                    <p class="smx-feature-toggle-desc">{{ $feature['description'] ?? '-' }}</p>
                                </div>
                                @if($isLocked)
                                    <span class="smx-feature-badge locked">Dikunci</span>
                                @else
                                    <span class="smx-feature-badge {{ $isEnabled ? 'on' : 'off' }}">{{ $isEnabled ? 'Aktif' : 'Nonaktif' }}</span>
                                @endif
                            </div>

                            <div class="smx-feature-toggle-meta">
                                <div class="smx-mini-box">
                                    <span>Feature Key</span>
                                    <strong>{{ $feature['key'] }}</strong>
                                </div>
                                <div class="smx-mini-box">
                                    <span>Route</span>
                                    <strong>{{ $routePatterns !== '' ? $routePatterns : '-' }}</strong>
                                </div>
                                <div class="smx-mini-box">
                                    <span>Nonaktif Sampai</span>
                                    <strong>{{ $feature['disabled_until_label'] ?? '-' }}</strong>
                                </div>
                                <div class="smx-mini-box">
                                    <span>Dinonaktifkan Oleh</span>
                                    <strong>{{ $feature['disabled_by_name'] ?: '-' }}</strong>
                                </div>
                            </div>

                            @if(!$isEnabled && !empty($feature['disable_reason']))
                                <div class="smx-mini-box">
                                    <span>Alasan Maintenance</span>
                                    <strong>{{ $feature['disable_reason'] }}</strong>
                                </div>
                            @endif

                            <div class="smx-toggle-form">
                                @if($isLocked)
                                    <button class="smx-btn soft block" type="button" disabled>Sistem Management selalu aktif</button>
                                @elseif($isEnabled)
                                    <form method="POST" action="{{ route('system-management.features.toggle-available') }}">
                                        @csrf
                                        <input type="hidden" name="feature_key" value="{{ $feature['key'] }}">
                                        <input type="hidden" name="is_enabled" value="0">
                                        <div class="smx-toggle-form-grid">
                                            <div>
                                                <label class="smx-label">Alasan maintenance</label>
                                                <input name="disable_reason" class="form-control" maxlength="500" placeholder="Contoh: Perbaikan data atau audit modul">
                                            </div>
                                            <div>
                                                <label class="smx-label">Nonaktif sampai</label>
                                                <input type="datetime-local" name="disabled_until" class="form-control" min="{{ $featureDisableMin }}" required>
                                            </div>
                                            <button class="smx-btn danger" type="submit">Nonaktifkan</button>
                                        </div>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('system-management.features.toggle-available') }}">
                                        @csrf
                                        <input type="hidden" name="feature_key" value="{{ $feature['key'] }}">
                                        <input type="hidden" name="is_enabled" value="1">
                                        <button class="smx-btn success block" type="submit">Tayangkan Fitur Sekarang</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="smx-muted">Belum ada daftar fitur di config.</div>
                    @endforelse
                </div>
            </div>
        </section>

        @if(!empty($featureFlags) && count($featureFlags) > 0)
            <section class="smx-panel">
                <div class="smx-panel-header">
                    <h2 class="smx-panel-title">Draft Feature Flag Developer AI</h2>
                    <span class="smx-muted">Draft fitur baru sebelum masuk daftar modul global</span>
                </div>
                <div class="smx-table-wrap">
                    <table class="table smx-table">
                        <thead><tr><th>Fitur</th><th>Status</th><th>Aksi</th></tr></thead>
                        <tbody>
                            @foreach($featureFlags as $feature)
                                <tr>
                                    <td><strong>{{ $feature->name }}</strong><br><code>{{ $feature->key }}</code></td>
                                    <td>{{ $feature->is_enabled ? 'Aktif' : 'Nonaktif' }}<br><span class="smx-muted">{{ $feature->status }}</span></td>
                                    <td>
                                        <form method="POST" action="{{ route('system-management.features.toggle', $feature) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="is_enabled" value="{{ $feature->is_enabled ? 0 : 1 }}">
                                            <button class="smx-btn soft" type="submit">{{ $feature->is_enabled ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @endif
    @endif

    @if($page === 'feature-access')
        <section class="smx-panel">
            <div class="smx-panel-header">
                <h2 class="smx-panel-title">Akses Fitur Keseluruhan</h2>
                <span class="smx-muted">Menu dan route ikut dikunci</span>
            </div>
            <div class="smx-panel-body">
                <div class="smx-feature-list">
                    @forelse($availableFeatures ?? [] as $feature)
                        <div class="smx-feature-item">
                            <div>
                                <h3>{{ $feature['name'] ?? $feature['key'] }}</h3>
                                <p>{{ $feature['description'] ?? '-' }}</p>
                                @if($feature['locked'] ?? false)
                                    <span class="smx-feature-badge locked">Dikunci</span>
                                @else
                                    <span class="smx-feature-badge {{ ($feature['is_enabled'] ?? true) ? 'on' : 'off' }}">
                                        {{ ($feature['is_enabled'] ?? true) ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                @endif
                            </div>
                            <div>
                                @if($feature['locked'] ?? false)
                                    <button class="smx-btn soft" type="button" disabled>Tetap Aktif</button>
                                @elseif($feature['is_enabled'] ?? true)
                                    <a class="smx-btn danger" href="{{ route('system-management.features') }}">Atur Maintenance</a>
                                @else
                                    <form method="POST" action="{{ route('system-management.feature-access.update') }}">
                                        @csrf
                                        <input type="hidden" name="feature_key" value="{{ $feature['key'] }}">
                                        <input type="hidden" name="is_enabled" value="1">
                                        <button class="smx-btn success" type="submit">Tayangkan</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="smx-muted">Belum ada daftar fitur.</div>
                    @endforelse
                </div>
            </div>
        </section>
    @endif

    @if($page === 'archives')
        <section class="smx-panel">
            <div class="smx-panel-header"><h2 class="smx-panel-title">Arsip Log Blast Terhapus</h2></div>
            <div class="smx-table-wrap">
                <table class="table smx-table">
                    <thead><tr><th>Asal</th><th>Target</th><th>Status</th><th>Error/Response</th><th>Dihapus Oleh</th><th>Diarsipkan</th></tr></thead>
                    <tbody>
                        @forelse($archivedBlastLogs ?? [] as $archive)
                            <tr>
                                <td>{{ $archive->channel ?? '-' }}<br><code>#{{ $archive->original_log_id }}</code></td>
                                <td>{{ $archive->target ?? '-' }}</td>
                                <td>{{ $archive->status ?? '-' }}<br><span class="smx-muted">{{ $archive->provider_status ?? '-' }}</span></td>
                                <td>{{ \Illuminate\Support\Str::limit($archive->error_message ?: $archive->response, 120) }}</td>
                                <td>{{ $archive->archivedBy?->name ?? '-' }}<br><span class="smx-muted">{{ $archive->archive_reason }}</span></td>
                                <td>{{ $archive->created_at?->timezone('Asia/Jakarta')->format('d/m/Y H:i:s') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center smx-muted">Belum ada arsip log terhapus.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @endif
</div>
@endsection
