<?php

declare(strict_types=1);

use App\Enums\User\UserRole;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$projectRoot = dirname(__DIR__, 2);
require $projectRoot . '/vendor/autoload.php';

$productionUrl = rtrim((string) (getenv('ROLE_ACCESS_PRODUCTION_URL') ?: 'https://soy.ypikpamjaya.com'), '/');
$localUrl = rtrim((string) (getenv('ROLE_ACCESS_LOCAL_URL') ?: 'http://127.0.0.1:8010'), '/');
$githubUrl = rtrim((string) (getenv('ROLE_ACCESS_GITHUB_URL') ?: 'https://github.com/admypikpamjaya/SISTEMOPERASIONAL'), '/');
$outputPath = __DIR__ . '/akses-link-dan-kredensial-role-soy-ypik.xlsx';
$timezone = new DateTimeZone('Asia/Jakarta');
$generatedAt = new DateTimeImmutable('now', $timezone);

foreach ([$productionUrl, $localUrl, $githubUrl] as $url) {
    if (filter_var($url, FILTER_VALIDATE_URL) === false) {
        throw new RuntimeException('URL konfigurasi tidak valid: ' . $url);
    }
}

$accounts = [
    [
        'role' => UserRole::USER->value,
        'name' => 'User YPIK',
        'email' => 'user@ypik.local',
        'password' => 'Password-123!',
        'login_path' => '/login',
        'landing_path' => '/dashboard',
        'manual' => 'manual-book-role-user-soy-ypik.pdf',
        'access' => 'Dasbor dan Diskusi.',
    ],
    [
        'role' => UserRole::ADMIN->value,
        'name' => 'Admin YPIK',
        'email' => 'admin@ypik.local',
        'password' => 'Password-123!',
        'login_path' => '/login',
        'landing_path' => '/dashboard',
        'manual' => 'manual-book-role-admin-soy-ypik.pdf',
        'access' => 'Pengumuman, Pengingat, Blast, Penerima, dan Templat.',
    ],
    [
        'role' => UserRole::IT_SUPPORT->value,
        'name' => 'IT Support YPIK',
        'email' => 'it.support@ypik.local',
        'password' => 'Password-123!',
        'login_path' => '/login',
        'landing_path' => '/dashboard',
        'manual' => 'manual-book-role-it-support-soy-ypik.pdf',
        'access' => 'Seluruh portal operasional kecuali konsol root Sistem Management.',
    ],
    [
        'role' => UserRole::ASSET_MANAGER->value,
        'name' => 'Asset Manager YPIK',
        'email' => 'asset.manager@ypik.local',
        'password' => 'Password-123!',
        'login_path' => '/login',
        'landing_path' => '/dashboard',
        'manual' => 'manual-book-role-asset-manager-soy-ypik.pdf',
        'access' => 'CRUD aset dan pengelolaan laporan pemeliharaan.',
    ],
    [
        'role' => UserRole::FINANCE->value,
        'name' => 'Finance YPIK',
        'email' => 'finance@ypik.local',
        'password' => 'Password-123!',
        'login_path' => '/login',
        'landing_path' => '/dashboard',
        'manual' => 'manual-book-role-finance-soy-ypik.pdf',
        'access' => 'Operasional Finance dan CRUD aset; tidak mengelola Kategori Finance.',
    ],
    [
        'role' => UserRole::PEMBINA->value,
        'name' => 'Pembina YPIK',
        'email' => 'pembina@ypik.local',
        'password' => 'Password-123!',
        'login_path' => '/login',
        'landing_path' => '/dashboard',
        'manual' => 'manual-book-role-pembina-soy-ypik.pdf',
        'access' => 'Akses baca lintas aset, maintenance, pengguna, finance, dan komunikasi.',
    ],
    [
        'role' => UserRole::BLASTING->value,
        'name' => 'Blasting YPIK',
        'email' => 'blasting@ypik.local',
        'password' => 'Password-123!',
        'login_path' => '/login',
        'landing_path' => '/admin/blast',
        'manual' => 'manual-book-role-blasting-soy-ypik.pdf',
        'access' => 'WhatsApp, Email, Tunggakan, Penerima, dan Templat.',
    ],
    [
        'role' => UserRole::QC->value,
        'name' => 'QC YPIK',
        'email' => 'qc@ypik.local',
        'password' => 'Password-123!',
        'login_path' => '/login',
        'landing_path' => '/dashboard',
        'manual' => 'manual-book-role-qc-soy-ypik.pdf',
        'access' => 'Akses baca aset dan laporan pemeliharaan.',
    ],
    [
        'role' => UserRole::SYSTEM_MANAGEMENT->value,
        'name' => 'Sistem Management YPIK',
        'email' => 'sistem.management@ypik.local',
        'password' => 'System-Management-123!',
        'login_path' => '/system-management/login',
        'landing_path' => '/system-management',
        'manual' => 'manual-book-role-sistem-management-soy-ypik.pdf',
        'access' => 'Seluruh portal dan konsol root Sistem Management.',
    ],
];

$enumRoles = array_map(static fn (UserRole $role): string => $role->value, UserRole::cases());
$accountRoles = array_column($accounts, 'role');
$sortedEnumRoles = $enumRoles;
$sortedAccountRoles = $accountRoles;
sort($sortedEnumRoles);
sort($sortedAccountRoles);

if ($sortedEnumRoles !== $sortedAccountRoles || count(array_unique($accountRoles)) !== count($accounts)) {
    throw new RuntimeException('Daftar akun tidak identik dengan enum role aplikasi.');
}

foreach ($accounts as $account) {
    if (!is_file(__DIR__ . '/' . $account['manual'])) {
        throw new RuntimeException('Manual PDF tidak ditemukan: ' . $account['manual']);
    }
}

$spreadsheet = new Spreadsheet();
$spreadsheet->getProperties()
    ->setCreator('SOY YPIK')
    ->setLastModifiedBy('SOY YPIK')
    ->setTitle('Akses Link dan Kredensial Role SOY YPIK')
    ->setSubject('Daftar akses awal untuk seluruh role')
    ->setDescription('Berisi URL, akun awal dari UserSeeder, manual role, dan repository GitHub.')
    ->setKeywords('SOY YPIK role akses login credential manual github')
    ->setCategory('Dokumentasi Internal');

$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Akses Role');
$sheet->setShowGridlines(false);
$sheet->getTabColor()->setRGB('0B4A7F');

$headers = [
    'No',
    'Role',
    'Nama Akun Seeder',
    'URL Website',
    'URL Login',
    'URL Halaman Awal',
    'Username / Email',
    'Password Awal Seeder',
    'Status Password',
    'Manual PDF',
    'URL GitHub',
    'Akses Utama / Catatan',
];
$lastColumn = 'L';
$headerRow = 5;
$firstDataRow = 6;
$lastDataRow = $firstDataRow + count($accounts) - 1;

$sheet->mergeCells('A1:' . $lastColumn . '1');
$sheet->setCellValue('A1', 'DAFTAR LINK DAN KREDENSIAL ROLE SOY YPIK');
$sheet->mergeCells('A2:' . $lastColumn . '2');
$sheet->setCellValue('A2', 'DOKUMEN INTERNAL TERBATAS — berisi password awal dalam plaintext. Jangan unggah workbook ini ke repository publik atau membagikannya melalui grup umum.');
$sheet->mergeCells('A3:' . $lastColumn . '3');
$sheet->setCellValue('A3', sprintf(
    'Dibuat %s WIB · URL production: %s · Akun bersumber dari database/seeders/UserSeeder.php',
    $generatedAt->format('d/m/Y H:i'),
    $productionUrl
));

foreach ($headers as $index => $header) {
    $column = chr(ord('A') + $index);
    $sheet->setCellValue($column . $headerRow, $header);
}

$hyperlinkColumns = ['D', 'E', 'F', 'J', 'K'];
foreach ($accounts as $index => $account) {
    $row = $firstDataRow + $index;
    $loginUrl = $productionUrl . $account['login_path'];
    $landingUrl = $productionUrl . $account['landing_path'];

    $sheet->setCellValue('A' . $row, $index + 1);
    $sheet->setCellValueExplicit('B' . $row, $account['role'], DataType::TYPE_STRING);
    $sheet->setCellValueExplicit('C' . $row, $account['name'], DataType::TYPE_STRING);
    $sheet->setCellValueExplicit('D' . $row, $productionUrl, DataType::TYPE_STRING);
    $sheet->setCellValueExplicit('E' . $row, $loginUrl, DataType::TYPE_STRING);
    $sheet->setCellValueExplicit('F' . $row, $landingUrl, DataType::TYPE_STRING);
    $sheet->setCellValueExplicit('G' . $row, $account['email'], DataType::TYPE_STRING);
    $sheet->setCellValueExplicit('H' . $row, $account['password'], DataType::TYPE_STRING);
    $passwordStatus = $account['role'] === UserRole::SYSTEM_MANAGEMENT->value
        ? 'Akun seeder/dokumen internal — belum diverifikasi live'
        : 'Cocok snapshot DB 04/05/2026 — belum diverifikasi live';
    $sheet->setCellValueExplicit('I' . $row, $passwordStatus, DataType::TYPE_STRING);
    $sheet->setCellValueExplicit('J' . $row, 'Buka manual ' . $account['role'], DataType::TYPE_STRING);
    $sheet->setCellValueExplicit('K' . $row, $githubUrl, DataType::TYPE_STRING);
    $sheet->setCellValueExplicit('L' . $row, $account['access'], DataType::TYPE_STRING);

    $sheet->getCell('D' . $row)->getHyperlink()->setUrl($productionUrl)->setTooltip('Buka website production');
    $sheet->getCell('E' . $row)->getHyperlink()->setUrl($loginUrl)->setTooltip('Buka halaman login');
    $sheet->getCell('F' . $row)->getHyperlink()->setUrl($landingUrl)->setTooltip('Buka halaman awal setelah login');
    $sheet->getCell('J' . $row)->getHyperlink()->setUrl($account['manual'])->setTooltip('Buka manual PDF relatif terhadap workbook');
    $sheet->getCell('K' . $row)->getHyperlink()->setUrl($githubUrl)->setTooltip('Buka repository GitHub');
}

$sheet->getStyle('A1:' . $lastColumn . '1')->applyFromArray([
    'font' => ['bold' => true, 'size' => 18, 'color' => ['argb' => 'FFFFFFFF']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF0B4A7F']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
]);
$sheet->getRowDimension(1)->setRowHeight(34);

$sheet->getStyle('A2:' . $lastColumn . '2')->applyFromArray([
    'font' => ['bold' => true, 'color' => ['argb' => 'FF8A1C1C']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFE8E8']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
]);
$sheet->getRowDimension(2)->setRowHeight(38);

$sheet->getStyle('A3:' . $lastColumn . '3')->applyFromArray([
    'font' => ['italic' => true, 'color' => ['argb' => 'FF334155']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFEAF3F8']],
    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
]);
$sheet->getRowDimension(3)->setRowHeight(30);

$sheet->getStyle('A' . $headerRow . ':' . $lastColumn . $headerRow)->applyFromArray([
    'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF11679A']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFD4E0E8']]],
]);
$sheet->getRowDimension($headerRow)->setRowHeight(34);

$sheet->getStyle('A' . $firstDataRow . ':' . $lastColumn . $lastDataRow)->applyFromArray([
    'alignment' => ['vertical' => Alignment::VERTICAL_TOP, 'wrapText' => true],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFD4E0E8']]],
]);
$sheet->getStyle('A' . $firstDataRow . ':A' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('H' . $firstDataRow . ':H' . $lastDataRow)->applyFromArray([
    'font' => ['bold' => true, 'color' => ['argb' => 'FF8A1C1C']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFF1D6']],
]);
$sheet->getStyle('I' . $firstDataRow . ':I' . $lastDataRow)->applyFromArray([
    'font' => ['color' => ['argb' => 'FF7C4A03']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFF8E8']],
]);

for ($row = $firstDataRow; $row <= $lastDataRow; $row++) {
    if (($row - $firstDataRow) % 2 === 1) {
        foreach (['A', 'B', 'C', 'D', 'E', 'F', 'G', 'J', 'K', 'L'] as $column) {
            $sheet->getStyle($column . $row)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFF5F9FC');
        }
    }

    foreach ($hyperlinkColumns as $column) {
        $sheet->getStyle($column . $row)->getFont()
            ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF0563C1'))
            ->setUnderline(Font::UNDERLINE_SINGLE);
    }
    $sheet->getRowDimension($row)->setRowHeight(54);
}

$columnWidths = [
    'A' => 6,
    'B' => 22,
    'C' => 28,
    'D' => 34,
    'E' => 45,
    'F' => 45,
    'G' => 36,
    'H' => 29,
    'I' => 38,
    'J' => 30,
    'K' => 52,
    'L' => 58,
];
foreach ($columnWidths as $column => $width) {
    $sheet->getColumnDimension($column)->setWidth($width);
}

$sheet->freezePane('A' . $firstDataRow);
$sheet->setAutoFilter('A' . $headerRow . ':' . $lastColumn . $lastDataRow);
$sheet->getPageSetup()
    ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
    ->setPaperSize(PageSetup::PAPERSIZE_A4)
    ->setFitToWidth(1)
    ->setFitToHeight(0);
$sheet->getPageMargins()->setTop(0.4)->setRight(0.3)->setBottom(0.4)->setLeft(0.3);
$sheet->getHeaderFooter()->setOddFooter('&LInternal Terbatas&CPage &P dari &N&R' . $generatedAt->format('d/m/Y'));
$sheet->setSelectedCell('A1');

$guide = $spreadsheet->createSheet();
$guide->setTitle('Petunjuk dan Sumber');
$guide->setShowGridlines(false);
$guide->getTabColor()->setRGB('D97706');
$guide->mergeCells('A1:D1');
$guide->setCellValue('A1', 'PETUNJUK PENGGUNAAN DAN SUMBER DATA');
$guide->mergeCells('A2:D2');
$guide->setCellValue('A2', 'PERINGATAN: workbook ini menyimpan password awal dalam plaintext. Simpan di lokasi terbatas dan rotasi password jika file pernah tersebar.');

$guideRows = [
    ['Item', 'Nilai', 'Sumber / Status', 'Catatan'],
    ['Website production', $productionUrl, 'docs/restict dokumen.md', 'Gunakan URL ini untuk akses deployment production.'],
    ['Login umum', $productionUrl . '/login', 'routes/web.php', 'Untuk seluruh role kecuali Sistem Management.'],
    ['Login Sistem Management', $productionUrl . '/system-management/login', 'routes/web.php', 'Wajib memakai jalur login khusus.'],
    ['Local development', $localUrl, '.env APP_URL', 'Hanya aktif bila server lokal sedang berjalan.'],
    ['Repository GitHub', $githubUrl, 'git remote origin', 'Repository proyek; jangan unggah workbook kredensial ini ke repository publik.'],
    ['Username dan password', 'Akun awal UserSeeder', 'database/seeders/UserSeeder.php', 'Delapan akun non-root cocok dengan snapshot DB 04/05/2026; akun Sistem Management bersumber dari seeder/dokumen internal.'],
    ['Status password', 'Belum diverifikasi live', 'Hash password tidak dapat dibalik', 'Jika password sudah diganti, gunakan prosedur reset resmi—jangan mencoba mengekstrak hash.'],
    ['Efek menjalankan seeder', 'Password akun seed diperbarui kembali', 'UserSeeder menetapkan password pada setiap run', 'Jangan menjalankan seeder pada production tanpa change plan dan backup.'],
    ['Manual PDF', 'Hyperlink relatif dari workbook', 'docs/manual-book/*.pdf', 'Pertahankan workbook dan PDF dalam folder yang sama agar link lokal tetap berfungsi.'],
    ['Tanggal pembuatan', $generatedAt->format('d/m/Y H:i') . ' WIB', 'Generator workbook', 'Regenerasi setelah akun, domain, route, atau remote Git berubah.'],
];

foreach ($guideRows as $rowIndex => $values) {
    $excelRow = $rowIndex + 4;
    foreach ($values as $columnIndex => $value) {
        $column = chr(ord('A') + $columnIndex);
        $guide->setCellValueExplicit($column . $excelRow, (string) $value, DataType::TYPE_STRING);
    }
}

$guide->getCell('B5')->getHyperlink()->setUrl($productionUrl);
$guide->getCell('B6')->getHyperlink()->setUrl($productionUrl . '/login');
$guide->getCell('B7')->getHyperlink()->setUrl($productionUrl . '/system-management/login');
$guide->getCell('B8')->getHyperlink()->setUrl($localUrl);
$guide->getCell('B9')->getHyperlink()->setUrl($githubUrl);

$guide->getStyle('A1:D1')->applyFromArray([
    'font' => ['bold' => true, 'size' => 18, 'color' => ['argb' => 'FFFFFFFF']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF0B4A7F']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
]);
$guide->getRowDimension(1)->setRowHeight(34);
$guide->getStyle('A2:D2')->applyFromArray([
    'font' => ['bold' => true, 'color' => ['argb' => 'FF8A1C1C']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFE8E8']],
    'alignment' => ['wrapText' => true, 'vertical' => Alignment::VERTICAL_CENTER],
]);
$guide->getRowDimension(2)->setRowHeight(38);
$guide->getStyle('A4:D4')->applyFromArray([
    'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF11679A']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFD4E0E8']]],
]);
$guide->getStyle('A5:D14')->applyFromArray([
    'alignment' => ['vertical' => Alignment::VERTICAL_TOP, 'wrapText' => true],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFD4E0E8']]],
]);
foreach (range(5, 9) as $row) {
    $guide->getStyle('B' . $row)->getFont()
        ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF0563C1'))
        ->setUnderline(Font::UNDERLINE_SINGLE);
}
foreach (['A' => 28, 'B' => 52, 'C' => 42, 'D' => 72] as $column => $width) {
    $guide->getColumnDimension($column)->setWidth($width);
}
foreach (range(5, 14) as $row) {
    $guide->getRowDimension($row)->setRowHeight(42);
}
$guide->freezePane('A5');
$guide->setAutoFilter('A4:D14');
$guide->setSelectedCell('A1');

$spreadsheet->setActiveSheetIndex(0);
$writer = new Xlsx($spreadsheet);
$writer->setPreCalculateFormulas(false);
$writer->save($outputPath);
$spreadsheet->disconnectWorksheets();
unset($spreadsheet);

if (!is_file($outputPath) || filesize($outputPath) < 8_000) {
    throw new RuntimeException('File XLSX tidak terbentuk atau terlalu kecil.');
}

$magic = file_get_contents($outputPath, false, null, 0, 4);
if ($magic !== "PK\x03\x04") {
    throw new RuntimeException('Magic header XLSX/ZIP tidak valid.');
}

$verification = IOFactory::load($outputPath);
if ($verification->getSheetCount() !== 2 || $verification->getSheetNames() !== ['Akses Role', 'Petunjuk dan Sumber']) {
    throw new RuntimeException('Struktur sheet workbook tidak sesuai.');
}

$verifiedSheet = $verification->getSheetByName('Akses Role');
if ($verifiedSheet === null || $verifiedSheet->getHighestDataRow() !== $lastDataRow) {
    throw new RuntimeException('Jumlah baris workbook tidak sesuai.');
}

$verifiedRoles = [];
foreach ($accounts as $index => $account) {
    $row = $firstDataRow + $index;
    $verifiedRoles[] = (string) $verifiedSheet->getCell('B' . $row)->getValue();

    $expectedLinks = [
        'D' => $productionUrl,
        'E' => $productionUrl . $account['login_path'],
        'F' => $productionUrl . $account['landing_path'],
        'J' => $account['manual'],
        'K' => $githubUrl,
    ];
    foreach ($expectedLinks as $column => $expectedUrl) {
        $actualUrl = $verifiedSheet->getCell($column . $row)->getHyperlink()->getUrl();
        if ($actualUrl !== $expectedUrl) {
            throw new RuntimeException(sprintf('Hyperlink tidak sesuai pada %s%d.', $column, $row));
        }
    }

    if ((string) $verifiedSheet->getCell('G' . $row)->getValue() !== $account['email']
        || (string) $verifiedSheet->getCell('H' . $row)->getValue() !== $account['password']) {
        throw new RuntimeException('Kredensial hasil workbook tidak sesuai sumber pada role ' . $account['role'] . '.');
    }
}

sort($verifiedRoles);
if ($verifiedRoles !== $sortedEnumRoles) {
    throw new RuntimeException('Role hasil workbook tidak sesuai enum aplikasi.');
}

$verification->disconnectWorksheets();
$hash = hash_file('sha256', $outputPath);
printf(
    "OK  %s  %d role  %d sheet  %d byte  SHA-256 %s\n",
    basename($outputPath),
    count($accounts),
    2,
    filesize($outputPath),
    $hash
);
