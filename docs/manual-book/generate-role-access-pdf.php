<?php

declare(strict_types=1);

use App\Enums\User\UserRole;

$projectRoot = dirname(__DIR__, 2);
require $projectRoot . '/vendor/autoload.php';

$productionUrl = rtrim((string) (getenv('ROLE_ACCESS_PRODUCTION_URL') ?: 'https://soy.ypikpamjaya.com'), '/');
$localUrl = rtrim((string) (getenv('ROLE_ACCESS_LOCAL_URL') ?: 'http://127.0.0.1:8010'), '/');
$githubUrl = rtrim((string) (getenv('ROLE_ACCESS_GITHUB_URL') ?: 'https://github.com/admypikpamjaya/SISTEMOPERASIONAL'), '/');
$outputPath = __DIR__ . '/akses-link-dan-kredensial-role-soy-ypik.pdf';
$timezone = new DateTimeZone('Asia/Jakarta');
$generatedAt = new DateTimeImmutable('now', $timezone);

foreach ([$productionUrl, $localUrl, $githubUrl] as $url) {
    if (filter_var($url, FILTER_VALIDATE_URL) === false) {
        throw new RuntimeException('URL konfigurasi tidak valid: ' . $url);
    }
}

$roleMetadata = [
    UserRole::USER->value => [
        'login_path' => '/login',
        'landing_path' => '/dashboard',
        'manual' => 'manual-book-role-user-soy-ypik.pdf',
        'access' => 'Dasbor dan Diskusi.',
    ],
    UserRole::ADMIN->value => [
        'login_path' => '/login',
        'landing_path' => '/dashboard',
        'manual' => 'manual-book-role-admin-soy-ypik.pdf',
        'access' => 'Pengumuman, Pengingat, Blast, Penerima, dan Templat.',
    ],
    UserRole::IT_SUPPORT->value => [
        'login_path' => '/login',
        'landing_path' => '/dashboard',
        'manual' => 'manual-book-role-it-support-soy-ypik.pdf',
        'access' => 'Seluruh portal operasional kecuali konsol root Sistem Management.',
    ],
    UserRole::ASSET_MANAGER->value => [
        'login_path' => '/login',
        'landing_path' => '/dashboard',
        'manual' => 'manual-book-role-asset-manager-soy-ypik.pdf',
        'access' => 'CRUD aset dan pengelolaan laporan pemeliharaan.',
    ],
    UserRole::FINANCE->value => [
        'login_path' => '/login',
        'landing_path' => '/dashboard',
        'manual' => 'manual-book-role-finance-soy-ypik.pdf',
        'access' => 'Operasional Finance dan CRUD aset; tidak mengelola Kategori Finance.',
    ],
    UserRole::PEMBINA->value => [
        'login_path' => '/login',
        'landing_path' => '/dashboard',
        'manual' => 'manual-book-role-pembina-soy-ypik.pdf',
        'access' => 'Akses baca lintas aset, maintenance, pengguna, finance, dan komunikasi.',
    ],
    UserRole::BLASTING->value => [
        'login_path' => '/login',
        'landing_path' => '/admin/blast',
        'manual' => 'manual-book-role-blasting-soy-ypik.pdf',
        'access' => 'WhatsApp, Email, Tunggakan, Penerima, dan Templat.',
    ],
    UserRole::QC->value => [
        'login_path' => '/login',
        'landing_path' => '/dashboard',
        'manual' => 'manual-book-role-qc-soy-ypik.pdf',
        'access' => 'Akses baca aset dan laporan pemeliharaan.',
    ],
    UserRole::SYSTEM_MANAGEMENT->value => [
        'login_path' => '/system-management/login',
        'landing_path' => '/system-management',
        'manual' => 'manual-book-role-sistem-management-soy-ypik.pdf',
        'access' => 'Seluruh portal dan konsol root Sistem Management.',
    ],
];

/** @return array<int, array<string, string>> */
function seededAccounts(string $seederPath, array $roleMetadata): array
{
    $source = file_get_contents($seederPath);
    if ($source === false) {
        throw new RuntimeException('UserSeeder tidak dapat dibaca.');
    }

    $defaultPattern = <<<'REGEX'
~\$defaultPassword\s*=\s*Hash::make\(\s*'([^']+)'\s*\)~
REGEX;
    if (!preg_match($defaultPattern, $source, $defaultMatch)) {
        throw new RuntimeException('Password default pada UserSeeder tidak ditemukan.');
    }
    $defaultPassword = stripcslashes($defaultMatch[1]);

    $accountPattern = <<<'REGEX'
~\[
    \s*'email'\s*=>\s*'(?<email>[^']+)'\s*,
    \s*'name'\s*=>\s*'(?<name>[^']+)'\s*,
    \s*'password'\s*=>\s*(?:
        (?<uses_default>\$defaultPassword)
        |
        Hash::make\(\s*'(?<password>[^']+)'\s*\)
    )\s*,
    \s*'role'\s*=>\s*UserRole::(?<role_case>[A-Z_]+)\s*,?
\s*\]
~x
REGEX;

    if (!preg_match_all($accountPattern, $source, $matches, PREG_SET_ORDER)) {
        throw new RuntimeException('Tidak ada akun yang berhasil dibaca dari UserSeeder.');
    }

    $roleCases = [];
    foreach (UserRole::cases() as $roleCase) {
        $roleCases[$roleCase->name] = $roleCase;
    }

    $accounts = [];
    $roleOccurrences = [];
    foreach ($matches as $match) {
        $roleCaseName = (string) $match['role_case'];
        $role = $roleCases[$roleCaseName] ?? null;
        if (!$role instanceof UserRole || !isset($roleMetadata[$role->value])) {
            throw new RuntimeException('Role UserSeeder tidak dikenali: ' . $roleCaseName);
        }

        $roleOccurrences[$role->value] = ($roleOccurrences[$role->value] ?? 0) + 1;
        $accounts[] = [
            'role' => $role->value,
            'name' => stripcslashes((string) $match['name']),
            'email' => stripcslashes((string) $match['email']),
            'password' => !empty($match['uses_default'])
                ? $defaultPassword
                : stripcslashes((string) $match['password']),
            'account_type' => $roleOccurrences[$role->value] === 1 ? 'Akun utama role' : 'Akun tambahan role',
            ...$roleMetadata[$role->value],
        ];
    }

    return $accounts;
}

function escapeHtml(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function fileUrl(string $path): string
{
    $normalized = str_replace('\\', '/', $path);
    $encoded = implode('/', array_map('rawurlencode', explode('/', $normalized)));

    return 'file:///' . str_replace('%3A', ':', $encoded);
}

function dataUri(string $path, string $mimeType): string
{
    $data = file_get_contents($path);
    if ($data === false) {
        throw new RuntimeException('Aset gambar tidak dapat dibaca: ' . $path);
    }

    return 'data:' . $mimeType . ';base64,' . base64_encode($data);
}

function accountStatus(array $account): string
{
    if ($account['role'] !== UserRole::SYSTEM_MANAGEMENT->value) {
        return 'Cocok snapshot DB 04/05/2026; belum diverifikasi live.';
    }

    return $account['account_type'] === 'Akun utama role'
        ? 'Bersumber dari seeder/dokumen internal; belum diverifikasi live.'
        : 'Akun tambahan pada UserSeeder terbaru; belum diverifikasi live.';
}

function removeTemporaryDirectory(string $path): void
{
    if (!is_dir($path) || !str_starts_with(basename($path), 'soy-role-access-')) {
        return;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($iterator as $item) {
        $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
    }
    rmdir($path);
}

$accounts = seededAccounts($projectRoot . '/database/seeders/UserSeeder.php', $roleMetadata);
$enumRoles = array_map(static fn (UserRole $role): string => $role->value, UserRole::cases());
$accountRoles = array_values(array_unique(array_column($accounts, 'role')));
$accountEmails = array_column($accounts, 'email');
$sortedEnumRoles = $enumRoles;
sort($sortedEnumRoles);
sort($accountRoles);

if ($sortedEnumRoles !== $accountRoles || count(array_unique($accountEmails)) !== count($accounts)) {
    throw new RuntimeException('Cakupan role atau keunikan email akun UserSeeder tidak valid.');
}

foreach ($accounts as $account) {
    if (!is_file(__DIR__ . '/' . $account['manual'])) {
        throw new RuntimeException('Manual PDF tidak ditemukan: ' . $account['manual']);
    }
}

$logos = [
    'ypik' => dataUri($projectRoot . '/public/images/logo_ypik.webp', 'image/webp'),
    'si' => dataUri($projectRoot . '/public/images/logo-si.png', 'image/png'),
    'pradita' => dataUri($projectRoot . '/public/images/logo-pradita.png', 'image/png'),
];

$styles = <<<'CSS'
@page { size: A4; margin: 0; }
* { box-sizing: border-box; }
html, body { margin: 0; padding: 0; font-family: Arial, Helvetica, sans-serif; color: #153047; background: #fff; }
body { font-size: 10.5pt; line-height: 1.42; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
a { color: #075f9c; text-decoration: underline; overflow-wrap: anywhere; }
.page { width: 210mm; height: 297mm; padding: 15mm 16mm 13mm; position: relative; overflow: hidden; page-break-after: always; background: #fff; }
.page:last-child { page-break-after: auto; }
.cover { background: linear-gradient(145deg, #f8fcff 0%, #e7f3fa 70%, #d9eaf4 100%); }
.cover::after { content: ''; position: absolute; width: 110mm; height: 110mm; right: -45mm; top: -48mm; border-radius: 50%; background: rgba(11,74,127,.1); }
.brand-row { display: flex; align-items: center; justify-content: space-between; position: relative; z-index: 1; }
.brand-primary { display: flex; align-items: center; gap: 4mm; }
.brand-primary img { width: 21mm; height: 21mm; object-fit: contain; }
.brand-name { color: #064475; font-size: 10pt; font-weight: 800; letter-spacing: .35px; }
.brand-sub { color: #537387; font-size: 7.5pt; margin-top: 1mm; }
.partner-logos { display: flex; align-items: center; gap: 4mm; }
.partner-logos img { max-width: 25mm; max-height: 12mm; object-fit: contain; }
.cover-main { margin-top: 60mm; max-width: 155mm; }
.eyebrow { display: inline-block; color: #075b91; border: .4mm solid #87b6d3; border-radius: 10mm; padding: 1.5mm 4mm; font-size: 8pt; font-weight: 800; letter-spacing: .7px; }
h1 { color: #064475; font-size: 29pt; line-height: 1.12; margin: 6mm 0 4mm; }
.cover-lead { color: #345d75; font-size: 13pt; max-width: 145mm; }
.metric-row { display: flex; gap: 4mm; margin-top: 9mm; }
.metric { min-width: 37mm; background: #fff; border: .35mm solid #c9dfe9; border-radius: 3mm; padding: 4mm; }
.metric strong { display: block; color: #07558a; font-size: 20pt; line-height: 1; }
.metric span { color: #607889; font-size: 8pt; }
.cover-bottom { position: absolute; left: 16mm; right: 16mm; bottom: 14mm; }
.meta { display: grid; grid-template-columns: 45mm 1fr; gap: 1.6mm 4mm; background: rgba(255,255,255,.88); border: .35mm solid #c8dce7; border-radius: 3mm; padding: 5mm; font-size: 9pt; }
.meta b { color: #123f5d; }
.classification { margin-top: 4mm; border-left: 1.2mm solid #d97706; background: #fff5df; padding: 3mm 4mm; color: #7b4500; font-size: 8.5pt; font-weight: 700; }
.page-head { display: flex; align-items: flex-end; justify-content: space-between; border-bottom: .7mm solid #0b4a7f; padding-bottom: 3mm; margin-bottom: 6mm; }
.page-head h2 { color: #064475; font-size: 20pt; margin: 0; }
.page-head .kicker { color: #14749f; font-size: 8pt; font-weight: 800; letter-spacing: .65px; text-transform: uppercase; }
.page-count { color: #637b8b; font-size: 8pt; }
.callout { border: .35mm solid #c9dfe9; background: #f4f9fc; border-radius: 3mm; padding: 4mm; margin: 4mm 0; }
.callout.warning { border-color: #edc56e; background: #fff8e9; color: #734900; }
.callout.danger { border-color: #e4a5a5; background: #fff1f1; color: #7b2020; }
.callout strong { display: block; margin-bottom: 1mm; }
.link-table, .source-table { width: 100%; border-collapse: collapse; margin-top: 4mm; }
.link-table th, .link-table td, .source-table th, .source-table td { border: .3mm solid #ccdae3; padding: 2.6mm 3mm; vertical-align: top; text-align: left; }
.link-table th, .source-table th { background: #0b5f91; color: #fff; font-size: 8.5pt; }
.link-table td:first-child, .source-table td:first-child { width: 39mm; font-weight: 700; color: #214d69; }
.account-grid { display: grid; grid-template-rows: 1fr 1fr; gap: 6mm; height: 235mm; }
.account-card { border: .45mm solid #bad2df; border-radius: 4mm; overflow: hidden; background: #fff; break-inside: avoid; }
.account-head { display: flex; align-items: center; justify-content: space-between; background: linear-gradient(90deg, #0b4a7f, #1176a7); color: #fff; padding: 3.4mm 4mm; }
.account-head h3 { margin: 0; font-size: 15pt; }
.account-type { border: .3mm solid rgba(255,255,255,.7); border-radius: 9mm; padding: 1mm 3mm; font-size: 7.5pt; font-weight: 700; }
.account-body { padding: 3.5mm 4mm; }
.identity-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 2.5mm 5mm; }
.field { border-bottom: .25mm solid #e0e9ee; padding-bottom: 1.5mm; min-width: 0; }
.field-label { color: #667d8b; text-transform: uppercase; font-size: 6.8pt; font-weight: 700; letter-spacing: .4px; }
.field-value { color: #163f59; font-weight: 700; margin-top: .7mm; overflow-wrap: anywhere; }
.credential { background: #fff5df; border: .35mm solid #edc56e; border-radius: 2mm; padding: 2.5mm 3mm; }
.credential .field-value { color: #8a291f; font-family: Consolas, 'Courier New', monospace; font-size: 10pt; }
.status { color: #765008; font-size: 8.2pt; }
.mini-links { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5mm 4mm; margin-top: 3mm; font-size: 8.3pt; }
.mini-links div { min-width: 0; }
.mini-links b { color: #224c67; display: block; }
.access-note { margin-top: 3mm; padding: 2.5mm 3mm; border-left: 1mm solid #1280ad; background: #edf7fb; font-size: 8.5pt; }
.footer { position: absolute; left: 16mm; right: 16mm; bottom: 6mm; border-top: .25mm solid #cfdee6; padding-top: 2mm; display: flex; justify-content: space-between; color: #70838e; font-size: 7pt; }
.checklist { margin: 5mm 0 0; padding: 0; list-style: none; }
.checklist li { margin-bottom: 3mm; padding: 3mm 4mm 3mm 11mm; border: .3mm solid #d1dee6; border-radius: 2mm; position: relative; }
.checklist li::before { content: '✓'; position: absolute; left: 3.5mm; top: 2.4mm; color: #087ca5; font-weight: 800; }
.signature { display: grid; grid-template-columns: repeat(3, 1fr); gap: 6mm; margin-top: 12mm; }
.signature div { border-top: .3mm solid #8ea6b5; padding-top: 2mm; text-align: center; color: #647c8b; font-size: 8pt; }
code { font-family: Consolas, 'Courier New', monospace; background: #edf3f6; border-radius: 1mm; padding: .4mm 1mm; }
CSS;

$accountCards = [];
foreach ($accounts as $index => $account) {
    $loginUrl = $productionUrl . $account['login_path'];
    $landingUrl = $productionUrl . $account['landing_path'];
    $manualUrl = fileUrl(__DIR__ . '/' . $account['manual']);
    $rootWarning = $account['role'] === UserRole::SYSTEM_MANAGEMENT->value
        ? '<div class="access-note"><b>Jalur khusus:</b> akun ini wajib masuk melalui login Sistem Management dan tidak boleh memakai login umum.</div>'
        : '';

    $accountCards[] = sprintf(
        '<article class="account-card">
            <div class="account-head"><h3>%s</h3><span class="account-type">%s</span></div>
            <div class="account-body">
                <div class="identity-grid">
                    <div class="field"><div class="field-label">Nama akun</div><div class="field-value">%s</div></div>
                    <div class="field"><div class="field-label">Username / email</div><div class="field-value">%s</div></div>
                    <div class="credential"><div class="field-label">Password awal UserSeeder</div><div class="field-value">%s</div></div>
                    <div class="field"><div class="field-label">Status password</div><div class="status">%s</div></div>
                </div>
                <div class="mini-links">
                    <div><b>Website</b><a href="%s">%s</a></div>
                    <div><b>Login</b><a href="%s">%s</a></div>
                    <div><b>Halaman awal</b><a href="%s">%s</a></div>
                    <div><b>Dokumentasi</b><a href="%s">Buka manual PDF role</a></div>
                    <div><b>GitHub</b><a href="%s">Repository proyek</a></div>
                </div>
                <div class="access-note"><b>Akses utama:</b> %s</div>
                %s
            </div>
        </article>',
        escapeHtml($account['role']),
        escapeHtml($account['account_type']),
        escapeHtml($account['name']),
        escapeHtml($account['email']),
        escapeHtml($account['password']),
        escapeHtml(accountStatus($account)),
        escapeHtml($productionUrl),
        escapeHtml($productionUrl),
        escapeHtml($loginUrl),
        escapeHtml($loginUrl),
        escapeHtml($landingUrl),
        escapeHtml($landingUrl),
        escapeHtml($manualUrl),
        escapeHtml($githubUrl),
        escapeHtml($account['access']),
        $rootWarning
    );
}

$accountPagesHtml = '';
foreach (array_chunk($accountCards, 2) as $pageIndex => $cards) {
    $start = $pageIndex * 2 + 1;
    $end = min($start + 1, count($accounts));
    $accountPagesHtml .= sprintf(
        '<section class="page">
            <div class="page-head"><div><div class="kicker">Daftar Akun Berdasarkan UserSeeder</div><h2>Akses Role dan Kredensial Awal</h2></div><div class="page-count">Akun %d–%d dari %d</div></div>
            <div class="account-grid">%s</div>
            <div class="footer"><span>SOY YPIK · Internal Terbatas</span><span>%s WIB</span></div>
        </section>',
        $start,
        $end,
        count($accounts),
        implode('', $cards),
        escapeHtml($generatedAt->format('d/m/Y H:i'))
    );
}

$html = sprintf(
    '<!doctype html>
    <html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <meta name="author" content="Yayasan YPIK">
        <title>Daftar Akses dan Kredensial Role SOY YPIK</title>
        <style>%s</style>
    </head>
    <body>
        <section class="page cover">
            <div class="brand-row">
                <div class="brand-primary"><img src="%s" alt="Logo YPIK"><div><div class="brand-name">SISTEM OPERASIONAL YAYASAN YPIK</div><div class="brand-sub">SOY YPIK PAM JAYA · Dokumen Akses Internal</div></div></div>
                <div class="partner-logos"><img src="%s" alt="Logo SI"><img src="%s" alt="Logo Pradita"></div>
            </div>
            <div class="cover-main">
                <span class="eyebrow">INTERNAL TERBATAS</span>
                <h1>Daftar Link dan Kredensial Role SOY YPIK</h1>
                <p class="cover-lead">Referensi akses untuk seluruh role, akun awal UserSeeder, halaman login, landing page, manual role, dan repository GitHub.</p>
                <div class="metric-row"><div class="metric"><strong>%d</strong><span>Role aplikasi</span></div><div class="metric"><strong>%d</strong><span>Akun UserSeeder</span></div><div class="metric"><strong>PDF</strong><span>Hyperlink aktif</span></div></div>
            </div>
            <div class="cover-bottom">
                <div class="meta"><b>Dibuat</b><span>%s WIB</span><b>Website</b><span><a href="%s">%s</a></span><b>GitHub</b><span><a href="%s">%s</a></span><b>Sumber akun</b><span><code>database/seeders/UserSeeder.php</code></span></div>
                <div class="classification">Dokumen ini memuat password awal dalam plaintext. Jangan unggah ke repository publik atau bagikan melalui grup umum.</div>
            </div>
        </section>

        <section class="page">
            <div class="page-head"><div><div class="kicker">Petunjuk Penggunaan</div><h2>Link Utama dan Status Data</h2></div><div class="page-count">Ringkasan</div></div>
            <div class="callout danger"><strong>Password bukan jaminan akses live</strong>Password pada dokumen ini berasal dari UserSeeder. Database runtime tidak tersedia saat dokumen dibuat, sehingga perubahan password pada deployment live tidak dapat dipastikan.</div>
            <table class="source-table">
                <thead><tr><th>Item</th><th>Link / Nilai</th><th>Catatan</th></tr></thead>
                <tbody>
                    <tr><td>Website production</td><td><a href="%s">%s</a></td><td>Domain production SOY YPIK.</td></tr>
                    <tr><td>Login umum</td><td><a href="%s/login">%s/login</a></td><td>Untuk seluruh role selain Sistem Management.</td></tr>
                    <tr><td>Login Sistem Management</td><td><a href="%s/system-management/login">%s/system-management/login</a></td><td>Jalur khusus role root.</td></tr>
                    <tr><td>Local development</td><td><a href="%s">%s</a></td><td>Hanya berfungsi bila server lokal aktif.</td></tr>
                    <tr><td>GitHub</td><td><a href="%s">%s</a></td><td>Repository proyek. Jangan simpan PDF ini di repository publik.</td></tr>
                </tbody>
            </table>
            <div class="callout warning"><strong>Cara membaca status password</strong>Delapan akun non-root cocok dengan snapshot database 04/05/2026, tetapi belum diverifikasi live. Akun Sistem Management bersumber dari seeder/dokumen internal terbaru dan juga belum diverifikasi live.</div>
            <div class="callout"><strong>Username pada form login</strong>Aplikasi menggunakan alamat email sebagai username. Role Sistem Management memakai halaman login khusus; role lain memakai login umum.</div>
            <div class="callout warning"><strong>Efek menjalankan UserSeeder</strong>UserSeeder menetapkan ulang password untuk setiap akun yang tercantum. Jangan menjalankan seeder pada production tanpa change plan, backup, dan persetujuan.</div>
            <div class="footer"><span>SOY YPIK · Internal Terbatas</span><span>%s WIB</span></div>
        </section>

        %s

        <section class="page">
            <div class="page-head"><div><div class="kicker">Keamanan dan Serah Terima</div><h2>Checklist Pengelolaan Akses</h2></div><div class="page-count">Penutup</div></div>
            <div class="callout danger"><strong>Rotasi password wajib</strong>Ganti seluruh password awal setelah akun diserahkan. Bila PDF ini pernah tersebar, anggap kredensial terpapar dan lakukan reset segera.</div>
            <ul class="checklist">
                <li>Verifikasi role, nama akun, email, dan jalur login sebelum menyerahkan akses.</li>
                <li>Kirim password melalui kanal aman dan terpisah dari pengiriman username.</li>
                <li>Minta pemilik akun mengganti password pada kesempatan pertama.</li>
                <li>Jangan mengunggah PDF ini ke GitHub, cloud publik, tiket umum, atau grup percakapan.</li>
                <li>Untuk akun Sistem Management, pastikan sedikitnya satu akun pemulihan sah tetap tersedia.</li>
                <li>Jika password awal tidak bekerja, gunakan prosedur reset resmi; jangan mencoba membalik hash database.</li>
                <li>Regenerasi dokumen setelah akun, route, domain, manual role, atau remote Git berubah.</li>
                <li>Hapus salinan lama secara aman setelah versi baru diterima dan diverifikasi.</li>
            </ul>
            <h3>Kontrol dokumen</h3>
            <table class="source-table"><tbody><tr><td>Jumlah role</td><td>%d</td><td>Sesuai enum UserRole.</td></tr><tr><td>Jumlah akun</td><td>%d</td><td>Sesuai UserSeeder saat PDF dibuat.</td></tr><tr><td>Format</td><td>PDF A4</td><td>Hyperlink website dan GitHub dapat diklik.</td></tr><tr><td>Klasifikasi</td><td>Internal Terbatas</td><td>Memuat kredensial awal plaintext.</td></tr></tbody></table>
            <div class="signature"><div>Disusun oleh</div><div>Diperiksa oleh</div><div>Disetujui oleh</div></div>
            <div class="footer"><span>SOY YPIK · Internal Terbatas</span><span>%s WIB</span></div>
        </section>
        <script>
            window.addEventListener("load", () => {
                const requestedPage = Number(new URLSearchParams(window.location.search).get("page"));
                if (requestedPage > 0) {
                    document.querySelectorAll(".page").forEach((page, index) => {
                        page.style.display = index === requestedPage - 1 ? "block" : "none";
                    });
                }
            });
        </script>
    </body>
    </html>',
    $styles,
    escapeHtml($logos['ypik']),
    escapeHtml($logos['si']),
    escapeHtml($logos['pradita']),
    count($enumRoles),
    count($accounts),
    escapeHtml($generatedAt->format('d/m/Y H:i')),
    escapeHtml($productionUrl),
    escapeHtml($productionUrl),
    escapeHtml($githubUrl),
    escapeHtml($githubUrl),
    escapeHtml($productionUrl),
    escapeHtml($productionUrl),
    escapeHtml($productionUrl),
    escapeHtml($productionUrl),
    escapeHtml($productionUrl),
    escapeHtml($productionUrl),
    escapeHtml($localUrl),
    escapeHtml($localUrl),
    escapeHtml($githubUrl),
    escapeHtml($githubUrl),
    escapeHtml($generatedAt->format('d/m/Y H:i')),
    $accountPagesHtml,
    count($enumRoles),
    count($accounts),
    escapeHtml($generatedAt->format('d/m/Y H:i'))
);

if (substr_count($html, 'class="account-card"') !== count($accounts)
    || str_contains($html, 'undefined')
    || str_contains($html, '>NaN<')) {
    throw new RuntimeException('Preflight HTML gagal.');
}

$qaPreviewPath = getenv('ROLE_ACCESS_QA_PREVIEW');
if (is_string($qaPreviewPath) && $qaPreviewPath !== '') {
    $redactedHtml = $html;
    foreach (array_unique(array_column($accounts, 'password')) as $password) {
        $redactedHtml = str_replace(escapeHtml((string) $password), '••••••••••••', $redactedHtml);
    }
    if (file_put_contents($qaPreviewPath, $redactedHtml, LOCK_EX) === false) {
        throw new RuntimeException('Preview QA tidak dapat ditulis.');
    }
}

$browserCandidates = array_values(array_filter([
    getenv('MANUAL_BROWSER') ?: null,
    'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
    'C:\\Program Files (x86)\\Google\\Chrome\\Application\\chrome.exe',
    'C:\\Program Files\\Microsoft\\Edge\\Application\\msedge.exe',
    'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe',
]));
$browser = null;
foreach ($browserCandidates as $candidate) {
    if (is_file($candidate)) {
        $browser = $candidate;
        break;
    }
}
if ($browser === null) {
    throw new RuntimeException('Chrome/Edge tidak ditemukan. Set MANUAL_BROWSER bila lokasinya berbeda.');
}

$temporaryDirectory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'soy-role-access-' . bin2hex(random_bytes(6));
if (!mkdir($temporaryDirectory, 0700, true) && !is_dir($temporaryDirectory)) {
    throw new RuntimeException('Direktori sementara tidak dapat dibuat.');
}
$htmlPath = $temporaryDirectory . DIRECTORY_SEPARATOR . 'akses-role.html';
$profilePath = $temporaryDirectory . DIRECTORY_SEPARATOR . 'chrome-profile';
mkdir($profilePath, 0700, true);
file_put_contents($htmlPath, $html, LOCK_EX);

if (is_file($outputPath)) {
    unlink($outputPath);
}

try {
    $command = [
        $browser,
        '--headless=new',
        '--disable-gpu',
        '--disable-extensions',
        '--disable-features=Translate',
        '--allow-file-access-from-files',
        '--run-all-compositor-stages-before-draw',
        '--virtual-time-budget=1500',
        '--no-pdf-header-footer',
        '--user-data-dir=' . $profilePath,
        '--print-to-pdf=' . $outputPath,
        fileUrl($htmlPath),
    ];
    $descriptorSpec = [
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];
    $process = proc_open($command, $descriptorSpec, $pipes, $projectRoot, null, ['bypass_shell' => true]);
    if (!is_resource($process)) {
        throw new RuntimeException('Browser headless tidak dapat dijalankan.');
    }
    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    $exitCode = proc_close($process);

    if ($exitCode !== 0 || !is_file($outputPath)) {
        throw new RuntimeException('Gagal mencetak PDF: ' . trim($stderr . "\n" . $stdout));
    }
} finally {
    removeTemporaryDirectory($temporaryDirectory);
}

$pdf = file_get_contents($outputPath);
if ($pdf === false || !str_starts_with($pdf, '%PDF-') || !str_contains(substr($pdf, -2048), '%%EOF')) {
    throw new RuntimeException('Signature atau EOF PDF tidak valid.');
}
if (strlen($pdf) < 100_000) {
    throw new RuntimeException('PDF terlalu kecil untuk paket akses lengkap.');
}

$pdfText = mb_convert_encoding($pdf, 'ISO-8859-1', 'ISO-8859-1');
$a4 = preg_match('/\/MediaBox\s*\[\s*0\s+0\s+(?:594(?:\.\d+)?|595(?:\.\d+)?)\s+(?:841(?:\.\d+)?|842(?:\.\d+)?)\s*\]/', $pdfText) === 1;
if (!$a4) {
    throw new RuntimeException('MediaBox A4 tidak ditemukan.');
}

$pageCount = preg_match_all('/\/Type\s*\/Page\b/', $pdfText);
$linkCount = preg_match_all('/\/Subtype\s*\/Link\b/', $pdfText);
if ($pageCount < 7 || $pageCount > 12 || $linkCount < 40) {
    throw new RuntimeException(sprintf('Struktur PDF tidak sesuai: %d halaman, %d link.', $pageCount, $linkCount));
}

printf(
    "OK  %s  %d akun  %d role  %d halaman  %d link  %d byte  SHA-256 %s\n",
    basename($outputPath),
    count($accounts),
    count($enumRoles),
    $pageCount,
    $linkCount,
    strlen($pdf),
    hash('sha256', $pdf)
);
