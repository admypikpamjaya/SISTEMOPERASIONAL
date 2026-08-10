#!/usr/bin/env node

/*
 * Generator Manual Book SOY YPIK
 *
 * Tidak membutuhkan dependency npm tambahan. HTML dibuat dari data terstruktur
 * di file ini, lalu dicetak ke PDF A4 dengan Chrome/Edge headless yang tersedia
 * pada mesin generator.
 */

const fs = require('fs');
const os = require('os');
const path = require('path');
const crypto = require('crypto');
const { spawnSync } = require('child_process');
const { pathToFileURL } = require('url');

const outputDir = __dirname;
const sourceDir = path.join(outputDir, 'source');
const projectRoot = path.resolve(outputDir, '..', '..');
const imageDir = path.join(projectRoot, 'public', 'images');

const MANUAL_VERSION = '1.0';
const APP_VERSION = '1.1';
const generatedDate = new Intl.DateTimeFormat('id-ID', {
    dateStyle: 'long',
    timeZone: 'Asia/Jakarta',
}).format(new Date());

function commandOutput(command, args, fallback = '-') {
    const result = spawnSync(command, args, {
        cwd: projectRoot,
        encoding: 'utf8',
        windowsHide: true,
    });

    return result.status === 0 && result.stdout.trim() ? result.stdout.trim() : fallback;
}

const gitRevision = commandOutput('git', ['rev-parse', '--short', 'HEAD']);
const gitBranch = commandOutput('git', ['branch', '--show-current']);
const workingTreeDirty = commandOutput('git', ['status', '--porcelain'], '') !== '';
const codeReference = workingTreeDirty
    ? `${gitBranch} · ${gitRevision} + snapshot working tree`
    : `${gitBranch} · ${gitRevision}`;
const footerCodeReference = `${gitRevision}${workingTreeDirty ? '+WT' : ''}`;

function imageData(filename, mimeType) {
    const data = fs.readFileSync(path.join(imageDir, filename));
    return `data:${mimeType};base64,${data.toString('base64')}`;
}

const assets = {
    ypik: imageData('logo_ypik.webp', 'image/webp'),
    si: imageData('logo-si.png', 'image/png'),
    pradita: imageData('logo-pradita.png', 'image/png'),
};

const roles = [
    {
        slug: 'user',
        label: 'User',
        short: 'Pengguna umum',
        owner: 'Staf/pengguna internal yang membutuhkan akses dasar aplikasi',
        mission: 'Memantau ringkasan yang tersedia dan berkolaborasi melalui Diskusi tanpa mengubah data modul bisnis.',
        landing: 'Dasbor',
        login: 'Login standar',
        permissionCount: 0,
        accessLevel: 'Akses dasar',
        menus: ['Dasbor', 'Diskusi', 'Keluar'],
        modules: ['public-maintenance'],
        flags: {},
        boundaries: [
            'Tidak memiliki permission bisnis secara default.',
            'Tautan statistik yang mengarah ke modul terbatas dapat menghasilkan 403.',
            'Perubahan tugas harus diikuti perubahan role/permission oleh pengelola berwenang.',
        ],
        routine: [
            'Masuk dan pastikan nama serta role pada header benar.',
            'Baca ringkasan Dasbor dan pesan penting pada Diskusi.',
            'Gunakan QR aset bila perlu mengirim laporan pemeliharaan publik.',
            'Keluar setelah pekerjaan selesai, terutama pada perangkat bersama.',
        ],
        escalation: 'Hubungi IT Support untuk masalah akun/akses; hubungi Asset Manager untuk tindak lanjut laporan aset.',
        permissionRows: [
            ['Dasbor', 'Lihat ringkasan umum', '—', 'Modul dinamis mengikuti permission'],
            ['Diskusi', 'Baca, kirim, balas, pin', 'Hapus pesan sendiri', 'Lampiran dan voice note didukung'],
            ['Modul bisnis', 'Tidak ada secara default', '—', 'Dapat berubah jika ada override'],
        ],
    },
    {
        slug: 'admin',
        label: 'Admin',
        short: 'Administrator komunikasi',
        owner: 'Tim administrasi dan komunikasi yayasan',
        mission: 'Menyiapkan penerima dan templat, mengirim komunikasi massal, serta mengelola pengumuman dan pengingat.',
        landing: 'Dasbor',
        login: 'Login standar',
        permissionCount: 15,
        accessLevel: 'Operasional komunikasi',
        menus: ['Dasbor', 'Diskusi', 'Pesan Massal', 'Pengingat', 'Keluar'],
        modules: ['blasting', 'communications'],
        flags: { blastWrite: true, recipientWrite: true, templateWrite: true, announcementWrite: true, reminderWrite: true },
        boundaries: [
            'Tidak dapat mengelola perangkat/provider WhatsApp atau akun SMTP pengirim.',
            'Tidak memiliki akses default ke aset, pengguna, finance, atau konsol sistem.',
            'Aksi hapus penerima/log/tagihan dapat berdampak luas; selalu periksa lingkup pilihan.',
        ],
        routine: [
            'Periksa kualitas dan validitas data penerima.',
            'Uji templat serta placeholder pada sampel kecil.',
            'Kirim kampanye setelah jumlah penerima dan isi pesan dikonfirmasi.',
            'Pantau log gagal/menunggu dan tindak lanjuti tanpa membuat pesan ganda.',
            'Perbarui pengumuman dan pengingat sesuai kalender operasional.',
        ],
        escalation: 'Masalah gateway atau akun email diteruskan ke IT Support/Sistem Management.',
        permissionRows: [
            ['Pengumuman', 'Baca', 'Buat, ubah, hapus', 'Permission kelola memakai create'],
            ['Pengingat', 'Baca', 'Buat, ubah, aktif/nonaktif', 'Permission kelola memakai send'],
            ['WhatsApp/Email/Tunggakan', 'Baca log/status pengiriman', 'Kirim dan tindak lanjuti kegagalan', 'Tidak mengelola infrastruktur pengirim'],
            ['Data Penerima', 'Baca', 'Tambah, ubah, impor, hapus', 'Siswa, karyawan, umum'],
            ['Templat Pesan', 'Baca', 'Tambah, ubah, aktif/nonaktif, hapus', 'WhatsApp dan Email'],
        ],
    },
    {
        slug: 'it-support',
        label: 'IT Support',
        short: 'Superadmin operasional',
        owner: 'Tim teknis operasional aplikasi',
        mission: 'Menjaga kelancaran seluruh modul bisnis, akun, integrasi pengiriman, dan tampilan global aplikasi.',
        landing: 'Dasbor',
        login: 'Login standar',
        permissionCount: 40,
        accessLevel: 'Seluruh permission portal',
        menus: ['Dasbor', 'Diskusi', 'Master Data Aset', 'Manajemen Aset & Pengguna', 'Pesan Massal', 'Pengingat', 'Tema Website', 'Finance', 'Keluar'],
        modules: ['asset', 'maintenance', 'user-management', 'finance', 'blasting', 'communications', 'platform-operations'],
        flags: { assetWrite: true, maintenanceWrite: true, userWrite: true, financeWrite: true, blastWrite: true, recipientWrite: true, templateWrite: true, announcementWrite: true, reminderWrite: true, systemOperator: true },
        boundaries: [
            'Tidak dapat membuka konsol khusus Sistem Management.',
            'Permission IT Support masih dapat dicabut melalui override Sistem Management.',
            'Tidak dapat mengganti password target IT Support lain dari Basis Data Pengguna.',
            'Perubahan provider, SMTP, kategori finance, dan tema berdampak ke banyak pengguna.',
        ],
        routine: [
            'Periksa widget superadmin dan penerima notifikasi pemeliharaan.',
            'Pastikan provider WhatsApp, perangkat aktif, antrean, dan akun email sehat.',
            'Tinjau laporan pemeliharaan serta permintaan akun/role.',
            'Pantau log/status pengiriman, finance, dan aktivitas login yang relevan.',
            'Dokumentasikan perubahan konfigurasi global dan hasil pengujiannya.',
        ],
        escalation: 'Maintenance global, restrict role, feature access, CMS, AI/API tester, dan audit root diteruskan ke Sistem Management.',
        permissionRows: [
            ['Aset & Pemeliharaan', 'Baca', 'CRUD aset; ubah/status/hapus laporan', 'Submit laporan baru tetap dari QR publik'],
            ['Pengguna', 'Baca', 'Tambah, ubah, hapus, reset', 'Riwayat masuk tersedia'],
            ['Finance', 'Baca', 'Generate, input/impor, invoice, depresiasi', 'Termasuk kategori finance'],
            ['Komunikasi', 'Baca', 'Kirim, CRUD penerima/templat', 'Termasuk provider/perangkat dan SMTP'],
            ['Tema', 'Baca', 'Simpan/reset tema global', 'Berlaku untuk semua pengguna'],
        ],
    },
    {
        slug: 'asset-manager',
        label: 'Asset Manager',
        short: 'Pengelola aset',
        owner: 'Petugas inventaris dan pemeliharaan aset',
        mission: 'Menjaga master aset tetap akurat dan memproses laporan pemeliharaan hingga status final.',
        landing: 'Dasbor',
        login: 'Login standar',
        permissionCount: 8,
        accessLevel: 'CRUD aset dan laporan pemeliharaan',
        menus: ['Dasbor', 'Diskusi', 'Master Data Aset', 'Manajemen Aset → Laporan Pemeliharaan', 'Keluar'],
        modules: ['asset', 'maintenance', 'public-maintenance'],
        flags: { assetWrite: true, maintenanceWrite: true },
        boundaries: [
            'Tidak mengelola akun pengguna, finance, blasting, atau konfigurasi sistem.',
            'Laporan pemeliharaan baru dikirim dari halaman detail publik hasil pemindaian QR.',
            'Penghapusan aset/laporan harus dilakukan hanya setelah verifikasi dan kebutuhan arsip dipenuhi.',
        ],
        routine: [
            'Periksa aset baru/perubahan data dan laporan berstatus Menunggu.',
            'Validasi kode aset, lokasi, bukti, biaya, PIC, dan deskripsi pekerjaan.',
            'Setujui atau tolak laporan setelah verifikasi; catat tindak lanjut melalui kanal resmi bila diperlukan.',
            'Ekspor laporan berkala dan cek konsistensi QR dengan master data.',
        ],
        escalation: 'Masalah akun ke IT Support; masalah nilai/jurnal aset ke Finance; masalah kebijakan ke Pembina.',
        permissionRows: [
            ['Aset', 'Baca', 'Tambah, impor, ubah, hapus', 'Semua kategori dan QR'],
            ['Laporan Pemeliharaan', 'Baca & ekspor', 'Ubah, status, kirim ulang, hapus', 'Tidak ada create internal'],
            ['Modul lain', 'Tidak ada', '—', 'Mengikuti override bila diterapkan'],
        ],
    },
    {
        slug: 'finance',
        label: 'Finance',
        short: 'Operator keuangan',
        owner: 'Tim keuangan/bendahara',
        mission: 'Mencatat transaksi, mengelola laporan dan faktur, menghitung penyusutan, serta menjaga keterlacakan data keuangan.',
        landing: 'Dasbor',
        login: 'Login standar',
        permissionCount: 16,
        accessLevel: 'CRUD aset dan operasional finance',
        menus: ['Dasbor', 'Diskusi', 'Finance (tanpa Kategori Finance)', 'Finance → Manajemen Aset', 'Keluar'],
        modules: ['asset', 'finance'],
        flags: { assetWrite: true, financeWrite: true },
        boundaries: [
            'Kategori Finance hanya dikelola IT Support atau Sistem Management.',
            'Tunggakan sekarang berada pada modul Blasting, bukan Finance.',
            'Kalkulasi penyusutan pada halaman saat ini bersifat manual dan menghasilkan log/jurnal.',
            'Faktur/invoice berstatus POSTED harus dikembalikan ke DRAFT sebelum diubah.',
        ],
        routine: [
            'Periksa faktur draft, jurnal posted, dan saldo ringkasan.',
            'Catat transaksi pada kategori dan periode yang benar.',
            'Pastikan total debit dan kredit seimbang sebelum posting.',
            'Tinjau hasil penyusutan, laporan, dan cuplikan sebelum diunduh/diserahkan.',
            'Koordinasikan kebutuhan kategori baru kepada IT Support/Sistem Management.',
        ],
        escalation: 'Kategori dan kendala teknis ke IT Support; temuan kebijakan/approval ke Pembina.',
        permissionRows: [
            ['Aset', 'Baca', 'Tambah, impor, ubah, hapus', 'Akses dari shortcut Finance'],
            ['Penyusutan', 'Baca log', 'Hitung', 'Log dapat diunduh'],
            ['Laporan/Statements/Bagan Akun', 'Baca & unduh', 'Generate, input, impor, ubah, hapus', 'Kategori master tidak dapat dikelola'],
            ['Faktur', 'Baca & unduh', 'Buat, ubah, hapus, post/draft, catatan', 'Gunakan jejak log'],
        ],
    },
    {
        slug: 'pembina',
        label: 'Pembina',
        short: 'Pengawas lintas modul',
        owner: 'Pembina/pengawas yayasan',
        mission: 'Melakukan peninjauan lintas aset, pengguna, keuangan, dan komunikasi tanpa mengubah data operasional.',
        landing: 'Dasbor',
        login: 'Login standar',
        permissionCount: 14,
        accessLevel: 'Read-only lintas modul',
        menus: ['Dasbor', 'Diskusi', 'Master Data Aset', 'Pemeliharaan & Basis Data Pengguna', 'Pesan Massal', 'Pengingat', 'Finance', 'Keluar'],
        modules: ['asset', 'maintenance', 'user-management', 'finance', 'blasting', 'communications', 'public-maintenance'],
        flags: { readOnlyGovernance: true },
        boundaries: [
            'Tidak dapat membuat, mengubah, mengirim, mem-posting, atau menghapus data bisnis.',
            'Riwayat Masuk ditolak karena ada pembatas role tambahan pada controller.',
            'Kontrol perangkat WhatsApp, akun email, kategori finance, dan tema tidak tersedia.',
            'Aksi publik melalui QR terpisah dari akses read-only portal.',
        ],
        routine: [
            'Tinjau indikator Dashboard untuk area yang perlu perhatian.',
            'Gunakan filter dan unduhan untuk menelaah aset, maintenance, finance, serta log/status pengiriman.',
            'Catat temuan beserta ID/kode/periode agar dapat ditindaklanjuti operator.',
            'Sampaikan koreksi kepada pemilik proses tanpa mencoba URL aksi langsung.',
        ],
        escalation: 'Aset ke Asset Manager; finance ke Finance; komunikasi ke Admin/Blasting; akses/teknis ke IT Support.',
        permissionRows: [
            ['Aset & Pemeliharaan', 'Baca & ekspor', 'Tidak ada', 'Read-only'],
            ['Basis Data Pengguna', 'Baca', 'Tidak ada', 'Riwayat Masuk tidak tersedia'],
            ['Finance', 'Baca & unduh', 'Tidak ada', 'Termasuk faktur, statements, snapshot'],
            ['Komunikasi', 'Baca log/data', 'Tidak ada', 'Pengumuman, pengingat, blast, penerima, templat'],
        ],
    },
    {
        slug: 'blasting',
        label: 'Blasting',
        short: 'Operator pesan massal',
        owner: 'Operator khusus WhatsApp dan Email massal',
        mission: 'Menyiapkan target, templat, dan kampanye pesan massal lalu memantau status pengirimannya.',
        landing: 'Pesan Massal',
        login: 'Login standar',
        permissionCount: 11,
        accessLevel: 'Operasional blast penuh',
        menus: ['Pesan Massal (WhatsApp, Tunggakan, Email, Penerima, Templat)', 'Keluar'],
        modules: ['blasting'],
        flags: { blastWrite: true, recipientWrite: true, templateWrite: true },
        boundaries: [
            'Sidebar sengaja dibatasi hanya ke Pesan Massal dan Keluar.',
            'Tidak dapat mengelola perangkat/provider WhatsApp atau akun SMTP pengirim.',
            'Tidak memiliki akses Pengumuman, Pengingat, Aset, Pengguna, Finance, atau Sistem Management.',
            'Gunakan retry secara selektif untuk mencegah penerima mendapat pesan ganda.',
        ],
        routine: [
            'Validasi penerima dan status perangkat/akun pengirim yang ditampilkan.',
            'Uji placeholder, pesan, subjek, dan lampiran pada sampel.',
            'Konfirmasi jumlah penerima sebelum mengirim.',
            'Pantau log, status gateway, dan reference campaign bila tampil; tangani gagal secara terukur.',
            'Rapikan data penerima serta templat setelah kampanye selesai.',
        ],
        escalation: 'Perangkat/provider WhatsApp dan akun SMTP diteruskan ke IT Support/Sistem Management.',
        permissionRows: [
            ['WhatsApp/Email/Tunggakan', 'Baca log/status pengiriman', 'Kirim dan tindak lanjuti kegagalan', 'Retry/clear sesuai izin send'],
            ['Data Penerima', 'Baca', 'Tambah, ubah, impor, pindah grup, hapus', 'Siswa, karyawan, umum'],
            ['Templat', 'Baca', 'Tambah, ubah, aktif/nonaktif, hapus', 'WhatsApp dan Email'],
            ['Infrastruktur pengirim', 'Lihat status seperlunya', 'Tidak dapat mengelola', 'Eskalasi ke operator sistem'],
        ],
    },
    {
        slug: 'qc',
        label: 'QC',
        short: 'Quality control aset',
        owner: 'Petugas quality control inventaris/pemeliharaan',
        mission: 'Memeriksa konsistensi data aset dan laporan pemeliharaan secara read-only lalu menyampaikan temuan.',
        landing: 'Dasbor',
        login: 'Login standar',
        permissionCount: 2,
        accessLevel: 'Read-only aset',
        menus: ['Dasbor', 'Diskusi', 'Master Data Aset', 'Manajemen Aset → Laporan Pemeliharaan', 'Keluar'],
        modules: ['asset', 'maintenance', 'public-maintenance'],
        flags: { readOnlyGovernance: true },
        boundaries: [
            'Tidak dapat menambah, mengimpor, mengubah, menghapus, atau mengubah status.',
            'Koreksi dilakukan melalui Asset Manager, bukan dengan mencoba endpoint aksi.',
            'Laporan publik via QR merupakan kanal intake dan terpisah dari pemeriksaan QC.',
        ],
        routine: [
            'Filter aset per kategori/unit dan periksa identitas, lokasi, nilai, serta QR.',
            'Tinjau laporan pemeliharaan berdasarkan rentang tanggal/status.',
            'Bandingkan bukti dan uraian kerja dengan master aset.',
            'Catat temuan menggunakan kode aset dan ID/tanggal laporan.',
            'Kirim hasil QC kepada Asset Manager melalui kanal yang disepakati.',
        ],
        escalation: 'Koreksi data/status ke Asset Manager; masalah akses ke IT Support.',
        permissionRows: [
            ['Aset', 'Baca, filter, detail, QR', 'Tidak ada', 'Read-only'],
            ['Laporan Pemeliharaan', 'Baca, filter, detail, ekspor', 'Tidak ada', 'Read-only'],
            ['Modul lain', 'Tidak ada', '—', 'Mengikuti override bila diterapkan'],
        ],
    },
    {
        slug: 'sistem-management',
        label: 'Sistem Management',
        short: 'Root system',
        owner: 'Pemegang otoritas tertinggi sistem',
        mission: 'Menjaga ketersediaan, keamanan, akses, fitur global, konfigurasi, dan pemulihan seluruh aplikasi.',
        landing: 'Dashboard Sistem',
        login: 'Login khusus Sistem Management',
        permissionCount: 40,
        accessLevel: 'Root system + seluruh permission portal',
        menus: ['Dasbor', 'Diskusi', 'Sistem Management', 'Seluruh Aset & Pengguna', 'Seluruh Pesan Massal', 'Pengingat', 'Tema', 'Seluruh Finance', 'Keluar'],
        modules: ['system-management', 'asset', 'maintenance', 'user-management', 'finance', 'blasting', 'communications', 'platform-operations'],
        flags: { assetWrite: true, maintenanceWrite: true, userWrite: true, financeWrite: true, blastWrite: true, recipientWrite: true, templateWrite: true, announcementWrite: true, reminderWrite: true, systemOperator: true, systemRoot: true },
        boundaries: [
            'Harus masuk melalui halaman login khusus; login standar akan menolak akun ini.',
            'Akses selalu penuh dan tidak dapat dicabut melalui Restrict Role.',
            'Tetap dapat masuk saat maintenance global atau feature access dimatikan.',
            'AI apply, API Tester, Custom CSS, reset password, dan perubahan akses adalah tindakan sensitif.',
        ],
        routine: [
            'Periksa Status Sistem, Audit, Alur Blast, serta anomali terbaru.',
            'Pastikan jalur pemulihan tetap tersedia sebelum maintenance/feature change.',
            'Catat kondisi awal, perubahan, pelaksana, waktu, dan hasil verifikasi.',
            'Uji akses dengan role terdampak setelah mengubah permission/fitur.',
            'Arsipkan bukti dan kembalikan konfigurasi bila indikator memburuk.',
        ],
        escalation: 'Perubahan berisiko tinggi harus mengikuti persetujuan pemilik sistem dan prosedur perubahan organisasi.',
        permissionRows: [
            ['Konsol Sistem', 'Baca seluruh panel', 'Maintenance, akses, fitur, CMS, AI/API, reset', 'Eksklusif role ini'],
            ['Seluruh modul bisnis', 'Baca', 'Seluruh aksi yang tersedia', 'Tidak terpengaruh override'],
            ['Infrastruktur pengirim', 'Baca status', 'Provider, perangkat, antrean, akun SMTP', 'Dampak eksternal'],
            ['Feature/Maintenance bypass', 'Tetap dapat masuk', 'Dapat memulihkan', 'Sistem Management terkunci aktif'],
        ],
    },
];

const styles = `
@page { size: A4; margin: 15mm 14mm 17mm; }
:root {
    --ink:#132238; --muted:#5d6b7d; --line:#d9e2ec; --soft:#f5f8fc;
    --navy:#0b3b68; --blue:#1463a5; --cyan:#0f8fa8; --green:#11845b;
    --amber:#b86a00; --red:#b42318; --white:#fff;
}
* { box-sizing:border-box; }
html { scroll-behavior:smooth; }
body { margin:0; color:var(--ink); background:#fff; font-family:Arial,Helvetica,sans-serif; font-size:10.6px; line-height:1.52; }
a { color:var(--blue); text-decoration:none; }
p { margin:0 0 7px; }
ul,ol { margin:5px 0 9px 18px; padding:0; }
li { margin:0 0 4px; }
code { padding:1px 4px; border:1px solid #dbe6ef; border-radius:4px; background:#eef4f8; font-family:Consolas,monospace; font-size:9.5px; }
.cover { min-height:260mm; padding:18mm 17mm 13mm; border:1px solid #cbd8e5; background:linear-gradient(150deg,#fff 0%,#f1f8fc 62%,#e5f3f6 100%); display:flex; flex-direction:column; justify-content:space-between; page-break-after:always; position:relative; overflow:hidden; }
.cover:before { content:""; position:absolute; width:110mm; height:110mm; border-radius:50%; right:-45mm; top:-45mm; background:linear-gradient(135deg,rgba(20,99,165,.16),rgba(15,143,168,.04)); }
.brand-row { display:flex; align-items:center; justify-content:space-between; gap:14px; position:relative; }
.brand-primary { display:flex; align-items:center; gap:12px; }
.brand-primary img { width:23mm; height:23mm; object-fit:contain; }
.brand-name { font-size:12px; font-weight:800; color:var(--navy); letter-spacing:.03em; }
.brand-sub { color:var(--muted); font-size:9.5px; }
.partner-logos { display:flex; align-items:center; gap:9px; }
.partner-logos img { max-height:10mm; max-width:31mm; object-fit:contain; }
.cover-main { position:relative; margin:10mm 0; }
.eyebrow { display:inline-block; padding:5px 10px; border:1px solid #9bc4d7; border-radius:999px; background:rgba(255,255,255,.72); color:var(--navy); font-size:9px; font-weight:800; letter-spacing:.12em; text-transform:uppercase; }
.cover h1 { margin:12px 0 8px; max-width:155mm; color:var(--navy); font-size:31px; line-height:1.13; }
.cover .subtitle { max-width:150mm; color:#355169; font-size:14px; line-height:1.5; }
.role-pill { display:inline-block; margin-top:14px; padding:7px 13px; border-radius:7px; background:var(--navy); color:#fff; font-size:12px; font-weight:800; }
.cover-meta { display:grid; grid-template-columns:38mm 1fr; gap:6px 12px; max-width:155mm; padding:12px 14px; border:1px solid #cddde8; border-radius:10px; background:rgba(255,255,255,.76); }
.cover-meta dt { font-weight:800; color:#3c5267; }
.cover-meta dd { margin:0; }
.classification { margin-top:10px; padding:9px 11px; border-left:4px solid var(--amber); background:#fff7e8; color:#754600; font-weight:700; }
.toc { page-break-after:always; }
.toc h2 { margin-top:0; }
.toc-list { columns:2; column-gap:14mm; list-style:none; margin-left:0; counter-reset:toc; }
.toc-list li { break-inside:avoid; border-bottom:1px dotted #c5d2df; padding:5px 0; counter-increment:toc; }
.toc-list li:before { content:counter(toc) ". "; color:var(--cyan); font-weight:800; }
.section { break-before:page; }
.section.first { break-before:auto; }
.section-kicker { color:var(--cyan); font-size:9px; font-weight:800; letter-spacing:.1em; text-transform:uppercase; }
h2 { margin:3px 0 10px; padding-bottom:6px; border-bottom:2px solid #d6e6ef; color:var(--navy); font-size:19px; line-height:1.25; break-after:avoid; }
h3 { margin:13px 0 6px; color:#23455f; font-size:13px; break-after:avoid; }
h4 { margin:9px 0 4px; color:#2f4b61; font-size:11px; break-after:avoid; }
.lead { margin-bottom:11px; color:#3d5367; font-size:11.4px; }
.grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:9px; margin:8px 0 11px; }
.grid-3 { display:grid; grid-template-columns:repeat(3,1fr); gap:8px; margin:8px 0 11px; }
.card { break-inside:avoid; padding:10px 11px; border:1px solid var(--line); border-radius:8px; background:var(--soft); }
.card-title { margin-bottom:4px; color:#223f58; font-weight:800; }
.stat { font-size:18px; font-weight:800; color:var(--blue); }
.muted { color:var(--muted); }
.small { font-size:9.4px; }
.badge { display:inline-block; margin:0 3px 3px 0; padding:2px 7px; border:1px solid #b8d4e4; border-radius:999px; background:#eef8fb; color:#15506e; font-size:8.8px; font-weight:800; }
.badge.write { border-color:#acd9c5; background:#eaf8f1; color:#17613f; }
.badge.read { border-color:#bfcce8; background:#eef3ff; color:#344e91; }
.badge.root { border-color:#ddbae8; background:#f8edfb; color:#71337f; }
.callout { break-inside:avoid; margin:9px 0 11px; padding:9px 11px; border-left:4px solid var(--blue); border-radius:5px; background:#edf6ff; }
.callout strong { display:block; margin-bottom:2px; color:#154c79; }
.callout.warning { border-color:var(--amber); background:#fff7e8; color:#67410b; }
.callout.warning strong { color:#7c4c00; }
.callout.danger { border-color:var(--red); background:#fff0ee; color:#6e241e; }
.callout.danger strong { color:#8f251c; }
.callout.success { border-color:var(--green); background:#ebf8f2; color:#245b45; }
.callout.success strong { color:#12603e; }
.breadcrumb { break-inside:avoid; margin:7px 0 10px; padding:7px 10px; border:1px solid #cddde7; border-radius:6px; background:#f8fbfd; color:#3b5368; font-weight:700; }
.breadcrumb span { color:var(--cyan); padding:0 5px; }
table { width:100%; border-collapse:collapse; margin:7px 0 12px; font-size:9.6px; }
thead { display:table-header-group; }
tr { break-inside:avoid; }
th,td { padding:6px 7px; border:1px solid var(--line); text-align:left; vertical-align:top; }
th { background:#eaf3f8; color:#1d4c6b; font-size:9px; letter-spacing:.025em; text-transform:uppercase; }
.steps { list-style:none; margin:7px 0 12px; counter-reset:step; }
.steps li { break-inside:avoid; position:relative; min-height:25px; margin:0 0 7px; padding:6px 8px 6px 34px; border:1px solid #dce5ed; border-radius:7px; background:#fff; counter-increment:step; }
.steps li:before { content:counter(step); position:absolute; left:8px; top:6px; width:19px; height:19px; border-radius:50%; background:var(--blue); color:#fff; font-size:9px; line-height:19px; text-align:center; font-weight:800; }
.steps b { color:#254760; }
.flow { display:flex; align-items:stretch; gap:5px; margin:9px 0 12px; }
.flow-item { flex:1; break-inside:avoid; padding:8px 7px; border:1px solid #cbdde7; border-radius:7px; background:#f5fafc; text-align:center; font-size:9.3px; }
.flow-arrow { align-self:center; color:var(--cyan); font-size:15px; font-weight:800; }
.checklist { list-style:none; margin-left:0; }
.checklist li { position:relative; padding-left:19px; }
.checklist li:before { content:"□"; position:absolute; left:0; top:-1px; color:var(--blue); font-size:14px; font-weight:800; }
.role-header { display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:9px; padding:10px 12px; border:1px solid #cddfe9; border-radius:9px; background:linear-gradient(135deg,#f5fafc,#edf6f8); }
.role-header-name { color:var(--navy); font-size:18px; font-weight:800; }
.matrix td:first-child { font-weight:800; color:#24465e; }
.signature { display:grid; grid-template-columns:1fr 1fr 1fr; gap:14mm; margin-top:20mm; text-align:center; }
.signature div:before { content:""; display:block; margin-bottom:21mm; border-top:1px solid #8293a4; }
.doc-footer { margin-top:14px; padding-top:7px; border-top:1px solid var(--line); color:var(--muted); font-size:8.8px; text-align:center; }
@media print {
    * { print-color-adjust:exact; -webkit-print-color-adjust:exact; }
    a { color:inherit; }
}
`;

function esc(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function table(headers, rows, className = '') {
    return `<table class="${esc(className)}"><thead><tr>${headers.map((header) => `<th>${header}</th>`).join('')}</tr></thead><tbody>${rows.map((row) => `<tr>${row.map((cell) => `<td>${cell}</td>`).join('')}</tr>`).join('')}</tbody></table>`;
}

function steps(items) {
    return `<ol class="steps">${items.map((item) => `<li>${item}</li>`).join('')}</ol>`;
}

function checklist(items) {
    return `<ul class="checklist">${items.map((item) => `<li>${item}</li>`).join('')}</ul>`;
}

function callout(tone, title, body) {
    return `<div class="callout ${esc(tone)}"><strong>${title}</strong>${body}</div>`;
}

function breadcrumb(items) {
    return `<div class="breadcrumb">${items.map(esc).join('<span>›</span>')}</div>`;
}

function flow(items) {
    return `<div class="flow">${items.map((item, index) => `${index ? '<div class="flow-arrow">›</div>' : ''}<div class="flow-item">${item}</div>`).join('')}</div>`;
}

function section(id, title, lead, html, kicker = 'Panduan Operasional') {
    return { id, title, html: `<div class="section-kicker">${kicker}</div><h2>${title}</h2>${lead ? `<p class="lead">${lead}</p>` : ''}${html}` };
}

function roleOverviewSection(role, compact = false) {
    const modeBadge = role.flags.systemRoot
        ? '<span class="badge root">ROOT SYSTEM</span>'
        : role.flags.readOnlyGovernance
            ? '<span class="badge read">READ-ONLY</span>'
            : role.permissionCount > 0
                ? '<span class="badge write">OPERASIONAL</span>'
                : '<span class="badge">AKSES DASAR</span>';

    const profile = `
        <div class="role-header">
            <div><div class="role-header-name">${esc(role.label)}</div><div class="muted">${esc(role.short)} · ${esc(role.owner)}</div></div>
            <div>${modeBadge}<span class="badge">${role.permissionCount} permission default</span></div>
        </div>
        <p>${esc(role.mission)}</p>
        ${table(
            ['Elemen', 'Ketentuan'],
            [
                ['Halaman awal', esc(role.landing)],
                ['Jalur masuk', esc(role.login)],
                ['Tingkat akses', esc(role.accessLevel)],
                ['Menu default', role.menus.map(esc).join(' · ')],
                ['Eskalasi utama', esc(role.escalation)],
            ],
            'matrix'
        )}
        ${callout('warning', 'Baseline, bukan jaminan tampilan deployment', 'Akses di dokumen ini mengikuti kode default. Sistem Management dapat menambah/mencabut permission, dan fitur global dapat dinonaktifkan. Menu aktual dapat berbeda. Otorisasi akhir tetap berada pada route/controller.')}
    `;

    if (compact) {
        return section(`role-${role.slug}`, `Role ${esc(role.label)}`, role.mission, `${profile}<h3>Batas tugas</h3><ul>${role.boundaries.map((item) => `<li>${esc(item)}</li>`).join('')}</ul><h3>Rutinitas yang disarankan</h3>${checklist(role.routine)}`, 'Profil Role');
    }

    return section(
        'profil-role',
        `Profil dan Ruang Lingkup Role ${esc(role.label)}`,
        'Gunakan halaman ini sebagai pemeriksaan cepat sebelum memulai pekerjaan.',
        `${profile}
        <h3>Matriks akses praktis</h3>
        ${table(['Modul', 'Akses lihat', 'Aksi', 'Catatan'], role.permissionRows, 'matrix')}
        <h3>Batas tanggung jawab</h3>
        <ul>${role.boundaries.map((item) => `<li>${esc(item)}</li>`).join('')}</ul>`
    );
}

function accessSection(role = null) {
    const systemRoot = Boolean(role?.flags?.systemRoot);
    const loginSteps = systemRoot
        ? [
            '<b>Buka login khusus.</b> Gunakan alamat <code>/system-management/login</code>; akun ini akan ditolak pada login standar.',
            '<b>Masukkan email dan password.</b> Opsi “Ingat Saya” tidak dipakai pada jalur khusus.',
            '<b>Verifikasi landing.</b> Setelah berhasil, halaman awal harus menampilkan Dashboard Sistem.',
            '<b>Periksa identitas.</b> Cocokkan nama dan role pada header sebelum menjalankan tindakan sensitif.',
        ]
        : [
            '<b>Buka halaman masuk.</b> Gunakan alamat aplikasi atau <code>/login</code>.',
            '<b>Masukkan email dan password.</b> Aktifkan “Ingat Saya” hanya pada perangkat pribadi/terkelola.',
            '<b>Pilih Masuk.</b> Sistem akan mengarahkan ke halaman awal sesuai role.',
            '<b>Periksa identitas.</b> Nama dan role tampil pada header. Hentikan pekerjaan jika role tidak sesuai.',
        ];

    return section(
        'akses-dan-navigasi',
        'Masuk, Navigasi, dan Keluar',
        'Prosedur dasar ini berlaku sebelum menjalankan modul mana pun.',
        `${callout('', 'Kredensial tidak dicantumkan', 'Manual ini tidak menyimpan email, password, token, atau kunci API. Minta kredensial melalui kanal resmi organisasi.')}
        ${role === null ? callout('warning', 'Pengecualian Sistem Management', 'Delapan role memakai login standar. Sistem Management wajib memakai <code>/system-management/login</code> dan akan ditolak pada login standar.') : ''}
        <h3>Masuk ke aplikasi</h3>
        ${steps(loginSteps)}
        ${systemRoot ? callout('danger', 'Jalur khusus wajib', 'Login standar akan mengeluarkan akun Sistem Management dan mengarahkannya ke halaman khusus. Ini adalah kontrol yang disengaja.') : ''}
        <h3>Bagian antarmuka</h3>
        ${table(
            ['Area', 'Fungsi', 'Cara pakai'],
            [
                ['Sidebar', 'Navigasi modul sesuai role', 'Klik menu induk untuk membuka submenu; menu yang tidak berizin tidak ditampilkan.'],
                ['Header pengguna', 'Menampilkan nama dan role aktif', 'Gunakan sebagai verifikasi konteks sebelum aksi.'],
                ['Mode tampilan', 'Mode Terang / Mode Gelap', 'Pilih ikon tema di header; pilihan tersimpan pada browser.'],
                ['Bahasa', 'Indonesia / Inggris', 'Pilih ikon bahasa di header. Label halaman mengikuti locale aktif.'],
                ['Keluar', 'Mengakhiri sesi', 'Gunakan menu Keluar; jangan hanya menutup tab pada komputer bersama.'],
            ]
        )}
        <h3>Memahami respons akses</h3>
        ${table(
            ['Gejala', 'Arti yang paling mungkin', 'Tindakan'],
            [
                ['Menu tidak terlihat', 'Role, permission, atau feature access tidak memenuhi syarat', 'Muat ulang sekali, lalu minta pengelola memeriksa permission/fitur.'],
                ['403 Forbidden', 'Akun terautentikasi tetapi tidak berhak melakukan aksi', 'Jangan mengulang melalui URL; eskalasi dengan nama halaman dan waktu.'],
                ['503 / maintenance', 'Web atau fitur sedang dinonaktifkan', 'Tunggu pengumuman pemulihan; Sistem Management memeriksa status.'],
                ['419 / sesi kedaluwarsa', 'CSRF/sesi sudah tidak valid', 'Muat ulang, login kembali, lalu ulangi input yang belum tersimpan.'],
                ['422 / validasi', 'Ada field atau format yang tidak sesuai', 'Baca pesan di layar dan koreksi field terkait.'],
            ]
        )}
        ${callout('warning', 'Menu bukan sumber otoritas akhir', 'Beberapa menu mempunyai allowlist role statis. Override permission saja belum tentu membuat menu muncul, sedangkan URL langsung tetap diperiksa oleh middleware/controller.')}`
    );
}

function sharedToolsSection(role = null) {
    const blastingOnly = role?.slug === 'blasting';
    const financeWidgets = Boolean(role?.flags?.financeWrite || role?.flags?.systemOperator || role?.flags?.systemRoot || role?.slug === 'pembina');
    const blastWidgets = Boolean(role?.flags?.blastWrite || role?.flags?.systemOperator || role?.flags?.systemRoot || role?.slug === 'pembina' || role?.slug === 'admin');
    const superadmin = Boolean(role?.flags?.systemOperator || role?.flags?.systemRoot);

    return section(
        'dasbor-dan-diskusi',
        'Dasbor dan Diskusi',
        blastingOnly
            ? 'Route Dasbor/Diskusi membutuhkan autentikasi, tetapi sidebar role Blasting sengaja hanya menampilkan lingkup Pesan Massal.'
            : 'Dua fitur bersama untuk ringkasan dan kolaborasi lintas role.',
        `<h3>Dasbor</h3>
        ${blastingOnly ? callout('warning', 'Tidak ada pada sidebar Blasting', 'Fokus kerja role ini adalah Pesan Massal. Gunakan menu yang tersedia; jangan mengandalkan URL langsung untuk alur rutin.') : breadcrumb(['Sidebar', 'Dasbor'])}
        ${role === null ? table(
            ['Widget', 'Role baseline'],
            [
                ['Statistik aset', 'Semua role pada Dasbor; link detail tetap memerlukan akses aset.'],
                ['Finance', 'Finance, Pembina, IT Support, Sistem Management.'],
                ['WhatsApp/Email', 'Admin, Blasting (via route langsung), Pembina, IT Support, Sistem Management.'],
                ['Distribusi email maintenance', 'IT Support dan Sistem Management.'],
            ]
        ) : ''}
        <ul>
            <li>Statistik aset per unit tetap menjadi ringkasan umum; tautan detail hanya dapat dibuka bila akun memiliki akses aset.</li>
            ${financeWidgets ? '<li>Widget saldo/grafik Finance tampil karena role memiliki akses baca laporan finance.</li>' : '<li>Widget Finance tidak tampil tanpa permission <code>finance_report.read</code>.</li>'}
            ${blastWidgets ? '<li>Grafik WhatsApp dan Email tampil karena role memiliki akses baca blasting.</li>' : '<li>Grafik blasting tidak tampil tanpa permission <code>admin_blast.read</code>.</li>'}
            ${superadmin ? '<li>Panel Distribusi Laporan Pemeliharaan tersedia untuk menambah/menghapus email tambahan. Email master selalu aktif.</li>' : ''}
            <li>Data grafik diperbarui berkala; gunakan tautan modul untuk pemeriksaan rinci.</li>
        </ul>
        <h3>Diskusi tim</h3>
        ${breadcrumb(['Sidebar', 'Diskusi'])}
        ${steps([
            '<b>Pilih kanal.</b> Kanal aktif ditampilkan pada area percakapan.',
            '<b>Cari atau baca pesan.</b> Daftar memuat pesan terbaru dan pesan yang sedang dipin.',
            '<b>Tulis pesan.</b> Isi teks, lampirkan berkas, atau rekam voice note. Minimal satu konten harus ada.',
            '<b>Balas bila perlu.</b> Pilih Balas pada pesan agar konteks ikut tercatat.',
            '<b>Pin informasi penting.</b> Pilih durasi 24 jam, 48 jam, 1 minggu, atau 1 bulan.',
            '<b>Hapus dengan tepat.</b> Pengguna hanya dapat menghapus pesan miliknya sendiri.',
        ])}
        ${callout('warning', 'Etika dan kerahasiaan', 'Jangan unggah password, token, data pribadi berlebihan, atau dokumen yang tidak sesuai audiens kanal. Pin hanya informasi yang benar-benar perlu terlihat lintas role.')}`
    );
}

function publicMaintenanceSection() {
    return section(
        'laporan-qr-publik',
        'Mengirim Laporan Pemeliharaan dari QR Aset',
        'Alur intake ini bersifat publik dan tidak bergantung pada permission pembuatan laporan di portal.',
        `${flow(['Pindai QR aset', 'Verifikasi identitas', 'Isi laporan + bukti', 'Kirim', 'Ditinjau pengelola'])}
        ${steps([
            '<b>Pindai QR.</b> Buka halaman detail publik aset melalui kamera/QR scanner.',
            '<b>Verifikasi aset.</b> Cocokkan kode aset, kategori, unit, lokasi, dan detail fisik sebelum mengisi.',
            '<b>Pilih “Kirim Laporan Pemeliharaan”.</b> Form laporan akan terbuka.',
            '<b>Isi field wajib.</b> Nama pekerja, tanggal pengerjaan, kondisi/masalah, deskripsi pengerjaan, dan nama PIC.',
            '<b>Isi biaya bila diketahui.</b> Nilai boleh kosong, tetapi jika diisi harus angka nol atau lebih.',
            '<b>Unggah bukti.</b> Foto wajib berupa JPG/JPEG/PNG/WEBP, maksimal 5 MB.',
            '<b>Periksa konfirmasi lalu Kirim.</b> Laporan masuk dengan status Menunggu; sistem kemudian mencoba mengirim notifikasi sesuai konfigurasi.',
        ])}
        ${table(
            ['Pemeriksaan sebelum kirim', 'Kriteria'],
            [
                ['Aset', 'Kode dan lokasi cocok dengan label fisik.'],
                ['Uraian masalah', 'Spesifik, dapat diverifikasi, tidak hanya “rusak”.'],
                ['Uraian pekerjaan', 'Jelaskan tindakan yang telah dilakukan/hasil pemeriksaan.'],
                ['Bukti foto', 'Jelas, relevan, tidak memuat data sensitif, ≤ 5 MB.'],
                ['PIC/tanggal', 'Nama dan tanggal aktual, bukan perkiraan.'],
            ]
        )}
        ${callout('warning', 'Perbedaan label dan validasi', 'Meskipun tampilan dapat memberi kesan lampiran opsional, validasi server saat ini mewajibkan satu foto bukti. Jika pengiriman ditolak, pastikan format dan ukuran foto sesuai.')}`
    );
}

function assetSection(role = null) {
    const canWrite = Boolean(role?.flags?.assetWrite);
    const financeNavigation = role?.slug === 'finance';
    const navigation = financeNavigation
        ? ['Sidebar', 'Finance', 'Manajemen Aset']
        : ['Sidebar', 'Master Data Aset', 'Pilih kategori'];

    const writeContent = canWrite ? `
        <h3>Menambah aset manual</h3>
        ${steps([
            '<b>Buka submenu kategori.</b> Halaman Master Data Aset lintas kategori bersifat read-only; CRUD dilakukan pada halaman kategori.',
            '<b>Pilih “Tambah Aset Baru”.</b> Jika kategori sudah dipilih dari submenu, kategori akan dikunci.',
            '<b>Isi identitas.</b> Kategori, kode aset, nomor serial, unit (TK/SD/Yayasan), lokasi, tahun pembelian, dan harga/nilai perolehan.',
            '<b>Isi detail teknis.</b> Field berubah sesuai kategori. Komputer memuat komponen monitor, motherboard, prosesor, RAM, penyimpanan, GPU, serta keyboard/mouse.',
            '<b>Simpan.</b> Periksa pesan sukses lalu cari kembali kode aset pada daftar.',
        ])}
        <h3>Impor aset</h3>
        ${steps([
            '<b>Masuk ke kategori tujuan</b> lalu pilih “Impor File Aset”.',
            '<b>Unduh templat resmi</b> untuk AC, Komputer, Kendaraan, Elektronik, Inventaris Ruangan, atau Bangunan Sarana Prasarana.',
            '<b>Isi tanpa mengubah struktur kolom/sheet.</b> Format yang didukung: XLSX, XLS, CSV.',
            '<b>Unggah dan proses.</b> AC/Komputer membaca semua sheet aktif; kategori lainnya membaca sheet <code>Data Aset</code>.',
            '<b>Tinjau hasil.</b> Kode yang sudah ada dapat diperbarui. Kategori tertentu membuat kode internal bila kode kosong/berulang.',
        ])}
        <h3>Mengubah, menghapus, dan QR</h3>
        <ul>
            <li><b>Edit:</b> buka ikon edit, koreksi master/detail teknis, lalu Perbarui Aset. Riwayat pemeliharaan tetap dipertahankan.</li>
            <li><b>Hapus:</b> gunakan hapus per baris atau Hapus Terpilih. Pastikan dependensi dan kebutuhan arsip sudah diperiksa.</li>
            <li><b>QR:</b> lihat QR, unduh gambar/PDF, atau Unduh Semua QR. Uji bahwa QR membuka detail aset yang benar.</li>
        </ul>
        ${callout('danger', 'Impor dan hapus adalah aksi berdampak besar', 'Simpan file sumber, catat waktu impor, tinjau jumlah baris, dan lakukan sampling. Jangan menghapus massal hanya untuk “mengulang” impor tanpa memahami perilaku update kode aset.')}` : `
        ${callout('warning', 'Mode read-only', 'Role ini dapat mencari, memfilter, membuka detail publik, melihat QR, dan meninjau data. Tombol tambah, impor, edit, serta hapus tidak tersedia atau akan ditolak.')}
        <h3>Prosedur pemeriksaan</h3>
        ${steps([
            '<b>Pilih kategori/unit</b> atau gunakan pencarian kode, lokasi, dan nama file impor.',
            '<b>Buka detail/QR</b> dan cocokkan identitas serta detail teknis dengan sumber pemeriksaan.',
            '<b>Catat temuan</b> menggunakan kode aset, kategori, unit, lokasi, dan field yang tidak sesuai.',
            '<b>Eskalasi</b> ke Asset Manager (atau Finance untuk nilai/jurnal) tanpa mengubah data.',
        ])}`;

    return section(
        'manajemen-aset',
        'Master Data dan Manajemen Aset',
        canWrite ? 'Panduan pencarian, registrasi, impor, perubahan, penghapusan, dan QR aset.' : 'Panduan peninjauan aset dalam mode read-only.',
        `${breadcrumb(navigation)}
        <h3>Mencari dan membaca data</h3>
        ${steps([
            '<b>Buka Master Data Aset</b> untuk ringkasan lintas kategori atau buka submenu kategori untuk lingkup spesifik.',
            '<b>Gunakan saringan.</b> Kategori, unit, rentang tanggal data terbaru, nama file impor, kata kunci, dan batas baris dapat digunakan bersama.',
            '<b>Buka detail.</b> Periksa kode/akun aset, serial, unit, lokasi, tahun, nilai perolehan, detail teknis, dan sumber impor.',
            '<b>Gunakan QR.</b> QR membuka detail publik dan riwayat pemeliharaan yang sudah Disetujui.',
        ])}
        ${table(
            ['Kategori', 'Detail khas yang perlu diperiksa'],
            [
                ['AC', 'Merek, ukuran/dimensi, unit/watt, nomor seri.'],
                ['Komputer', 'Merek/spesifikasi/serial per komponen.'],
                ['Kendaraan', 'Polisi/rangka/mesin, BPKB/STNK/pajak, kilometer, PIC, kondisi.'],
                ['Elektronik', 'Jenis/nama, merek/model, spesifikasi/serial, PIC, kondisi.'],
                ['Inventaris Ruangan', 'Lokasi, jenis/nama/bahan/ukuran, jumlah, harga satuan, kondisi.'],
                ['Bangunan Sarana Prasarana', 'Nama/jenis, luas/volume, dokumen, nilai buku, penanggung jawab.'],
            ]
        )}
        ${writeContent}`
    );
}

function maintenanceSection(role = null) {
    const canWrite = Boolean(role?.flags?.maintenanceWrite);
    const mutationContent = canWrite ? `
        <h3>Memproses laporan</h3>
        ${steps([
            '<b>Buka detail.</b> Periksa identitas aset, nama pekerja, tanggal, masalah, pekerjaan, PIC, biaya, bukti, dan status.',
            '<b>Koreksi bila perlu.</b> Ubah field yang diizinkan lalu pilih Simpan.',
            '<b>Tetapkan status.</b> Gunakan Setujui atau Tolak setelah verifikasi. Status tersedia: Menunggu, Disetujui, Ditolak.',
            '<b>Kirim ulang notifikasi bila diperlukan.</b> Email master selalu ikut; pilih email dasbor dan/atau isi email manual untuk pengiriman satu kali.',
            '<b>Hapus hanya jika sah.</b> Konfirmasi bahwa data tidak lagi diperlukan dan bukti audit sudah disimpan.',
        ])}
        ${callout('warning', 'Dampak status', 'Halaman detail publik aset hanya menampilkan riwayat pemeliharaan berstatus Disetujui. Jangan menyetujui sebelum kode aset, pekerjaan, dan bukti benar.')}` : `
        ${callout('warning', 'Mode read-only', 'Role ini dapat memfilter, membuka detail, dan mengekspor laporan. Perubahan isi, status, notifikasi ulang, dan hapus tidak diizinkan.')}
        <h3>Prosedur tinjauan</h3>
        ${steps([
            '<b>Saring laporan</b> berdasarkan tanggal, status, dan kata kunci.',
            '<b>Buka detail</b> dan cocokkan identitas aset, uraian masalah/pekerjaan, PIC, biaya, serta bukti.',
            '<b>Catat temuan</b> dengan kode aset, tanggal, status, dan field yang perlu koreksi.',
            '<b>Eskalasi</b> kepada Asset Manager; jangan mencoba URL ubah/status langsung.',
        ])}`;

    return section(
        'laporan-pemeliharaan',
        'Laporan Pemeliharaan',
        canWrite ? 'Kelola laporan dari intake QR sampai status dan notifikasi final.' : 'Tinjau dan ekspor laporan tanpa mengubah data.',
        `${breadcrumb(['Sidebar', 'Manajemen Aset', 'Laporan Pemeliharaan'])}
        ${flow(['Laporan QR masuk', 'Menunggu', 'Verifikasi detail', 'Disetujui / Ditolak', 'Ekspor & tindak lanjut'])}
        <h3>Filter dan ekspor</h3>
        <ul>
            <li>Gunakan tanggal mulai/sampai, status, dan kata kunci; pilih Terapkan Filter.</li>
            <li>Pilih baris bila ekspor hanya untuk laporan tertentu.</li>
            <li>Jika tidak ada baris dipilih, ekspor Excel/PDF mengikuti seluruh filter aktif.</li>
            <li>Reset Filter untuk kembali ke lingkup penuh.</li>
        </ul>
        ${mutationContent}`
    );
}

function userManagementSection(role = null) {
    const canWrite = Boolean(role?.flags?.userWrite);
    const canViewLoginHistory = Boolean(role?.flags?.systemOperator || role?.flags?.systemRoot);
    const writeContent = canWrite ? `
        <h3>Tambah atau ubah pengguna</h3>
        ${steps([
            '<b>Pilih “Tambah Pengguna”.</b> Isi nama, email, dan role yang tepat.',
            '<b>Simpan dan amankan password sementara.</b> Akun baru memakai password awal dari konfigurasi (sama sampai konfigurasi berubah), sehingga wajib segera diganti dan hanya dibagikan lewat kanal aman.',
            '<b>Edit bila ada perubahan tugas.</b> Periksa dampak menu/permission setelah mengubah role.',
            '<b>Hapus hanya akun yang benar-benar dinonaktifkan.</b> Pastikan kepemilikan data dan kebutuhan audit ditangani.',
        ])}
        ${callout('danger', 'Hapus pengguna bukan deaktivasi', 'Aksi hapus adalah hard delete. Riwayat login target dan data creator tertentu seperti pengumuman/pengingat dapat ikut terhapus melalui cascade. Gunakan hanya setelah retensi, kepemilikan data, dan persetujuan diverifikasi.')}
        ${callout('danger', 'Lindungi akun Sistem Management', 'Jangan mengubah role, mengirim reset, mengganti password, atau menghapus akun Sistem Management tanpa otorisasi pemilik akses root. Kontrol backend CRUD umum dapat tetap menerima aksi tersebut; pastikan sedikitnya satu akun pemulihan yang sah tetap tersedia.')}
        <h3>Password</h3>
        <ul>
            <li><b>Reset link:</b> kirim tautan reset ke email pengguna bila jalur email tersedia. Link berlaku 60 menit dan pengiriman dibatasi sekitar satu kali per 60 detik.</li>
            <li><b>Kelola password langsung:</b> hanya IT Support/Sistem Management, minimal 8 karakter pada modul ini.</li>
            <li>Password lama tidak dapat dilihat karena tersimpan sebagai hash. Password baru hanya terlihat saat dibuat dan harus dibagikan lewat kanal aman.</li>
            ${role?.slug === 'it-support' ? '<li>IT Support tidak dapat mengganti password target IT Support lain dari halaman ini.</li>' : ''}
            ${role?.flags?.systemRoot ? '<li>Sistem Management dapat mengganti password target IT Support; gunakan hanya dengan otorisasi.</li>' : ''}
        </ul>` : `
        ${callout('warning', 'Mode read-only pengguna', 'Anda dapat mencari dan melihat nama, email, serta role. Beberapa form/tombol mungkin masih tampak pada view, tetapi endpoint tambah, edit, hapus, atau reset tetap menolak 403. Tampilan kontrol bukan bukti izin.')}`;

    return section(
        'basis-data-pengguna',
        'Basis Data Pengguna dan Riwayat Masuk',
        canWrite ? 'Kelola siklus akun dengan perhatian khusus pada role dan kredensial.' : 'Tinjau daftar pengguna tanpa mengubah akun.',
        `${breadcrumb(['Sidebar', 'Manajemen Aset', 'Basis Data Pengguna'])}
        <h3>Mencari akun</h3>
        <ul>
            <li>Gunakan kata kunci nama/email, lalu periksa role pada tabel.</li>
            <li>Aksi akun sendiri dan akun tertentu dapat dikunci untuk mencegah kehilangan akses.</li>
            <li>Jangan menafsirkan tidak adanya tombol sebagai error; tombol mengikuti permission dan pembatas role.</li>
        </ul>
        ${writeContent}
        <h3>Riwayat Masuk</h3>
        ${canViewLoginHistory
            ? '<p>Buka <b>Riwayat Masuk</b>, cari nama/email/role/IP/browser, lalu periksa waktu login. Gunakan data hanya untuk kebutuhan dukungan dan audit yang sah.</p>'
            : callout('warning', 'Tidak tersedia untuk role ini', 'Walaupun Basis Data Pengguna dapat dibaca, Riwayat Masuk memiliki pembatas tambahan dan hanya dapat digunakan Admin, IT Support, atau Sistem Management yang juga memiliki permission baca pengguna. Pada baseline, Pembina akan ditolak.')}
        ${callout('danger', 'Hindari lockout', 'Sebelum mengubah role atau password akun berprivilege tinggi, pastikan ada akun pemulihan yang sah dan catat siapa yang menyetujui perubahan.')}`
    );
}

function financeSection(role = null) {
    const canWrite = Boolean(role?.flags?.financeWrite);
    const canManageCategories = Boolean(role?.flags?.systemOperator || role?.flags?.systemRoot);

    const writeFinance = canWrite ? `
        <h3>Entri laporan dan cuplikan</h3>
        ${steps([
            '<b>Buka Entri Laporan Keuangan.</b> Pilih tipe periode (harian/bulanan/tahunan), kategori finance, tanggal/bulan/tahun, dan saldo awal.',
            '<b>Isi baris.</b> Pilih Pemasukan/Pengeluaran, kode akun, nama akun, nomor faktur (opsional), deskripsi, nominal, dan tanda penyusutan bila relevan.',
            '<b>Tambah baris seperlunya</b> lalu validasi periode, kategori, klasifikasi, serta nominal.',
            '<b>Simpan/generate.</b> Buka hasil dan Cuplikan untuk memastikan total pemasukan, pengeluaran, penyusutan, serta saldo akhir.',
            '<b>Edit atau hapus cuplikan</b> hanya saat periode dan jejak persetujuan mengizinkan; unduh dokumen final untuk arsip.',
        ])}
        ${callout('warning', 'Baris penyusutan diisi manual', 'Entri/Cuplikan laporan tidak otomatis mengambil histori dari kalkulator Penyusutan Aset. Cocokkan log kalkulator dan masukkan baris penyusutan secara eksplisit bila diperlukan.')}
        <h3>Lembar Saldo, Laba Rugi, dan Buku Besar</h3>
        ${steps([
            '<b>Pilih jenis laporan dan sumber data.</b> Gunakan sumber Sistem, Impor, atau Gabungan sesuai tujuan, lalu pilih batch impor bila relevan.',
            '<b>Atur filter periode/kategori</b> sebelum membaca atau mengelola baris.',
            '<b>Untuk impor, unduh templat resmi</b>, isi sesuai kolom, lalu unggah. Tinjau batch dan jumlah baris setelah proses.',
            '<b>Untuk entri manual, isi akun, label/tanggal/referensi, serta nominal/debit-kredit sesuai halaman.</b>',
            '<b>Gunakan pemindahan kategori massal secara hati-hati.</b> Pilih baris, kategori tujuan, lalu verifikasi hasil dengan filter ulang.',
            '<b>Simpan mapping akun</b> bila klasifikasi laporan membutuhkan pemetaan, lalu unduh laporan untuk validasi akhir.',
        ])}
        ${callout('', 'Validasi Buku Besar manual', 'Tipe OPENING tidak boleh memiliki debit/kredit. Tipe ENTRY harus memakai opening balance 0 dan tepat salah satu debit atau kredit bernilai lebih dari 0.')}
        <h3>Bagan Akun</h3>
        <ul>
            <li>Cari kode/nama akun dan periksa jenis serta nomor klasifikasi sebelum membuat transaksi.</li>
            <li>Tambah atau ubah akun dengan kode unik dan klasifikasi yang benar.</li>
            <li>Hapus klasifikasi hanya jika tidak merusak pemetaan yang telah digunakan; periksa log aktivitas.</li>
        </ul>
        ${callout('danger', 'Hapus klasifikasi bersifat massal', 'Untuk klasifikasi non-core, aksi hapus menghapus seluruh akun pada nomor klasifikasi tersebut, bukan hanya label kelompok. Verifikasi jumlah dan dependensi sebelum konfirmasi.')}
        <h3>Faktur / entri jurnal</h3>
        ${flow(['Buat DRAFT', 'Isi baris debit/kredit', 'Validasi seimbang', 'POSTED', 'Unduh / catatan / audit'])}
        ${steps([
            '<b>Pilih Faktur → Baru.</b> Nomor faktur dibuat otomatis.',
            '<b>Isi informasi utama.</b> Tanggal akuntansi, kategori finance, tipe Pemasukan/Pengeluaran, jurnal, dan referensi.',
            '<b>Isi minimal satu baris jurnal.</b> Kategori aset (opsional), akun, rekanan, label, distribusi analitik, debit, dan kredit.',
            '<b>Pastikan total debit = total kredit.</b> Gunakan Simpan Draft bila masih perlu pemeriksaan; gunakan Catat/Post setelah final.',
            '<b>Kelola status.</b> POSTED terkunci. Gunakan “Kembalikan ke Draft” sebelum koreksi yang sah.',
            '<b>Gunakan catatan dan unduhan.</b> Catatan menambah jejak tindak lanjut; unduh PDF/Excel sesuai kebutuhan.',
        ])}
        ${callout('danger', 'Posting adalah titik kontrol', 'Jangan menggunakan “Post semua draft” sebelum memfilter dan meninjau jumlah draft. Periksa kategori, tanggal, jurnal, referensi, serta keseimbangan setiap entri.')}` : `
        ${callout('warning', 'Mode read-only Finance', 'Role ini dapat membuka dashboard, filter laporan, melihat cuplikan/faktur/log, serta mengunduh dokumen. Tombol generate, impor, tambah, edit, post/draft, catatan, dan hapus tidak tersedia.')}
        <h3>Prosedur peninjauan</h3>
        ${steps([
            '<b>Pilih kategori dan periode</b> pada Dashboard Finance atau halaman laporan.',
            '<b>Bandingkan ringkasan</b> pemasukan, pengeluaran, penyusutan, saldo, serta jurnal terbaru.',
            '<b>Buka detail/cuplikan/faktur</b> dan periksa sumber, status DRAFT/POSTED, referensi, serta nilai debit-kredit.',
            '<b>Unduh dokumen</b> bila diperlukan untuk pemeriksaan formal.',
            '<b>Catat anomali</b> dengan kategori, periode, nomor faktur/jurnal, dan nominal lalu eskalasi ke Finance.',
        ])}`;

    const categoryContent = canManageCategories ? `
        <h3>Kategori Finance (khusus operator sistem)</h3>
        ${breadcrumb(['Sidebar', 'Finance', 'Kategori Finance'])}
        <ul>
            <li>Buat kategori berdiri sendiri atau gabungan, isi nama, tipe/sumber, anggota, dan deskripsi.</li>
            <li>Kategori gabungan menghitung data anggotanya secara dinamis; mengubah anggota mengubah hasil laporan berikutnya tanpa memindahkan data lama.</li>
            <li>Sembunyikan kategori untuk mencegah pemakaian baru sambil mempertahankan histori.</li>
            <li>Hapus hanya kategori yang belum dipakai data finance.</li>
        </ul>
        ${callout('warning', 'Dampak mapping', 'Sebelum mengubah anggota kategori gabungan, simpan hasil pembanding dan uji laporan yang bergantung pada kategori tersebut.')}` : `
        ${callout('', 'Kategori Finance', 'Role ini menggunakan kategori yang sudah tersedia. Pembuatan, perubahan, visibilitas, dan penghapusan kategori hanya untuk IT Support/Sistem Management.')}`;

    return section(
        'finance',
        'Operasional Finance',
        canWrite ? 'Dari kategori, penyusutan, entri laporan, statements, bagan akun, hingga siklus faktur.' : 'Panduan pemeriksaan laporan keuangan dalam mode read-only.',
        `${breadcrumb(['Sidebar', 'Finance'])}
        <h3>Dasbor Finance</h3>
        <ul>
            <li>Gunakan kategori dan periode yang sama saat membandingkan saldo, pemasukan, pengeluaran, penyusutan, jurnal, serta cuplikan.</li>
            <li>Kartu laporan hanya muncul bila permission baca terkait tersedia.</li>
            <li>Buka detail dari kartu, lalu kembali ke dasbor setelah menyelesaikan pemeriksaan.</li>
        </ul>
        ${categoryContent}
        <h3>Penyusutan aset (kalkulator manual)</h3>
        ${canWrite ? steps([
            '<b>Pilih kategori finance</b> sebagai tujuan hasil jurnal.',
            '<b>Saring kategori aset</b> bila diperlukan lalu pilih aset dari basis data.',
            '<b>Periksa nilai perolehan.</b> Nilai diambil dari harga aset bila tersedia dan dapat disesuaikan.',
            '<b>Pilih periode dari/sampai.</b> Umur manfaat bulan dihitung dari rentang dan tetap harus ditinjau.',
            '<b>Pilih Hitung Penyusutan.</b> Periksa hasil per bulan, periode manfaat, waktu WIB, dan jurnal.',
            '<b>Buka log</b> untuk bukti perhitungan dan unduh PDF jika diperlukan.',
        ]) : '<p>Pilih filter aset/kategori dan buka log perhitungan. Tinjau nilai perolehan, rentang, umur manfaat, hasil bulanan, waktu penghitung, dan jurnal; unduh log bila diperlukan.</p>'}
        ${callout('warning', 'Bukan closing otomatis', 'Halaman <b>Penyusutan Aset</b> saat ini adalah kalkulator manual beserta log/jurnal. Jangan mengasumsikan period closing otomatis sudah berjalan.')}
        ${writeFinance}`
    );
}

function blastingSection(role = null) {
    const canWrite = Boolean(role?.flags?.blastWrite);
    const canManageRecipients = Boolean(role?.flags?.recipientWrite);
    const canManageTemplates = Boolean(role?.flags?.templateWrite);
    const canOperateInfrastructure = Boolean(role?.flags?.systemOperator || role?.flags?.systemRoot);

    const sendContent = canWrite ? `
        <h3>Mengirim WhatsApp massal</h3>
        ${steps([
            '<b>Periksa provider dan status.</b> Pastikan Wablas aktif atau gateway/perangkat internal terhubung.',
            '<b>Tentukan penerima.</b> Pilih dari basis data, tambah nomor manual, atau impor Excel. Nomor dianjurkan dalam format <code>62...</code>.',
            '<b>Pilih isi.</b> Gunakan templat basis data, pesan global, atau pesan manual per penerima. Isi placeholder harus tersedia pada data target.',
            '<b>Pilih perangkat pengiriman.</b> Perangkat siswa/orang tua, karyawan, dan manual dapat dibedakan bila konfigurasi menyediakan.',
            '<b>Lampirkan berkas bila perlu.</b> PDF/gambar maksimal 5 MB untuk lampiran umum atau file khusus penerima.',
            '<b>Konfirmasi jumlah dan kirim.</b> Sistem memproses langsung; jadwal/delay pada halaman ini dinonaktifkan.',
            '<b>Pantau Log Aktivitas.</b> Terkirim baru final setelah gateway menyelesaikan job atau memberi ID pesan.',
        ])}
        ${callout('warning', 'Cegah pemblokiran dan pesan ganda', 'Uji pada sampel, batasi volume sesuai kebijakan provider, dan jangan retry log yang status akhirnya belum terverifikasi.')}
        <h3>Mengirim Email massal</h3>
        ${steps([
            '<b>Pilih akun pengirim.</b> Gunakan akun aktif dari Kontrol Email atau fallback MAIL yang disetujui.',
            '<b>Tambah penerima.</b> Pilih basis data, ketik email manual, atau impor file yang sesuai.',
            '<b>Isi subjek dan isi.</b> Pengumuman dapat mengisi otomatis; mode global/templat/manual per penerima tetap harus diperiksa.',
            '<b>Tambahkan lampiran.</b> Email mendukung beberapa file sesuai batas validasi aplikasi/provider.',
            '<b>Konfirmasi jumlah lalu kirim.</b> Pantau terkirim/gagal dan statistik pembukaan bila tracking tersedia.',
        ])}
        <h3>Tunggakan siswa</h3>
        ${flow(['Manual / Excel / sinkron siswa', 'Pencocokan', 'Tinjau nilai & periode', 'Pilih draft/gagal', 'Kirim WhatsApp'])}
        <ul>
            <li>Masukkan manual atau impor/sinkron dari penerima siswa; periksa nama, kelas, periode, nilai, telepon, VA, dan status cocok.</li>
            <li>Buat templat bawaan bila belum ada, lalu tinjau placeholder dan perangkat aktif.</li>
            <li>Kirim baris terpilih atau semua status Rancangan/Gagal; batas jumlah dapat diisi.</li>
            <li>“Hapus Semua Tagihan” tidak dapat dibatalkan. Ekspor/arsipkan bila data masih diperlukan.</li>
        </ul>
        <h3>Log aktivitas dan reference campaign</h3>
        <ul>
            <li>Gunakan filter/pencarian untuk mengidentifikasi channel, target, waktu, status provider, serta error.</li>
            <li>Retry hanya kegagalan yang jelas. Hapus/clear log menghilangkan bukti operasional dari tampilan aktif.</li>
            <li>UI WhatsApp/Email menampilkan Activity Log, bukan daftar campaign terpisah. Reference campaign dapat muncul pada status/log. Kontrol Pause/Resume/Stop tidak tersedia pada UI; endpoint backend bukan prosedur pengguna.</li>
        </ul>` : `
        ${callout('warning', 'Mode read-only Blasting', 'Role ini dapat membuka halaman, data penerima/templat, Activity Log, dan status pengiriman. Pengiriman, retry, clear, serta perubahan data tidak diizinkan.')}
        <h3>Prosedur peninjauan pengiriman</h3>
        ${steps([
            '<b>Pilih channel</b> WhatsApp atau Email dan tentukan rentang/keyword bila tersedia.',
            '<b>Bandingkan total</b> target, menunggu, terkirim, gagal, dan status provider/gateway.',
            '<b>Buka data sumber</b> penerima/templat untuk menilai kualitas nomor, email, dan placeholder tanpa mengubahnya.',
            '<b>Catat ID/reference, waktu, channel, jumlah, serta error</b> lalu eskalasi ke operator blasting.',
        ])}`;

    const recipientContent = canManageRecipients ? `
        <h3>Data penerima</h3>
        ${table(
            ['Kelompok', 'Data utama', 'Catatan'],
            [
                ['Siswa', 'Nama, kelas, jenjang/tahun/status, wali, WA 1/2, email, catatan', 'Dapat pindah kelas/kelompok massal dengan riwayat.'],
                ['Koperasi Tirta', 'Nama karyawan dan kontak', 'Sumber legacy khusus koperasi.'],
                ['Karyawan YPIK', 'Nama dan kontak karyawan terbaru', 'Terpisah dari data legacy Pam Jaya.'],
                ['YPIK Pam Jaya', 'Data legacy karyawan', 'Gunakan hanya untuk kebutuhan sumber lama.'],
                ['Umum', 'Nama, instansi/pekerjaan, WA, email, event, sertifikat, catatan', 'Nomor WhatsApp wajib; email opsional.'],
            ]
        )}
        ${steps([
            '<b>Filter dan cari</b> sebelum menambah agar duplikasi terdeteksi.',
            '<b>Tambah manual</b> atau unduh templat resmi lalu impor XLSX/XLS/CSV.',
            '<b>Tinjau valid/invalid/lengkap/kurang</b> dan koreksi format kontak.',
            '<b>Gunakan aksi massal dengan lingkup eksplisit.</b> Periksa baris terpilih versus semua hasil filter.',
            '<b>Ekspor kontak/riwayat</b> bila diperlukan sebelum penghapusan besar.',
        ])}` : `
        <h3>Data penerima</h3><p>Gunakan filter dan indikator validitas untuk meninjau siswa, karyawan, YPIK/Pam Jaya, serta penerima umum. Perubahan dan impor tidak tersedia pada mode ini.</p>`;

    const templateContent = canManageTemplates ? `
        <h3>Templat pesan</h3>
        ${steps([
            '<b>Pilih kanal</b> WhatsApp atau Email.',
            '<b>Buat nama dan isi yang jelas.</b> Gunakan placeholder yang tersedia pada sumber penerima.',
            '<b>Simpan dan uji.</b> Lakukan preview dengan data contoh yang tidak sensitif.',
            '<b>Aktif/nonaktifkan</b> sesuai masa berlaku; hapus hanya jika tidak lagi dibutuhkan sebagai referensi.',
        ])}` : '<h3>Templat pesan</h3><p>Filter berdasarkan kanal, lalu tinjau nama, isi, placeholder, dan status aktif. Perubahan tidak tersedia.</p>';

    const infraContent = canOperateInfrastructure ? `
        <h3>Perangkat/provider WhatsApp</h3>
        ${breadcrumb(['Pesan Massal', 'Kelola Perangkat'])}
        <ul>
            <li>Pilih provider Wablas atau Gateway sesuai layanan yang disetujui.</li>
            <li>Untuk Gateway: buat ID unik, hubungkan, pindai QR, aktifkan perangkat, ubah nama, reconnect/disconnect, atau hapus.</li>
            <li>Membersihkan antrean menghapus job menunggu/tertunda/selesai/gagal; job aktif dipertahankan untuk mencegah duplikasi.</li>
            <li>Reset semua perangkat menghapus sesi dan mewajibkan pemindaian QR ulang.</li>
        </ul>
        <h3>Kontrol akun Email</h3>
        ${breadcrumb(['Pesan Massal', 'Kontrol Email'])}
        <ul>
            <li>Tambah Gmail (preset <code>smtp.gmail.com</code>, 587/TLS, App Password) atau SMTP custom dari provider.</li>
            <li>Isi label, alamat pengirim, from name, host, port, enkripsi, username, password, reply-to, timeout, dan limit harian.</li>
            <li>Aktifkan akun, kirim email tes, periksa kesehatan/error, lalu jadikan pengirim utama.</li>
            <li>Jangan menyalin password SMTP/token ke tiket, chat, screenshot, atau manual.</li>
        </ul>` : `
        ${callout('', 'Infrastruktur pengirim dikelola terpisah', 'Kelola Perangkat dan Kontrol Email hanya tersedia untuk IT Support/Sistem Management. Jika status tidak siap, catat pesan/error dan eskalasi; jangan meminta kredensial lewat chat.')}`;

    return section(
        'pesan-massal',
        'Pesan Massal, Penerima, dan Templat',
        canWrite ? 'Alur end-to-end untuk WhatsApp, Email, Tunggakan, target, templat, Activity Log, dan status pengiriman.' : 'Panduan pemeriksaan blasting tanpa menjalankan aksi pengiriman.',
        `${breadcrumb(['Sidebar', 'Pesan Massal'])}
        ${flow(['Data penerima valid', 'Templat/isi final', 'Provider siap', 'Kirim', 'Pantau & rekonsiliasi'])}
        ${sendContent}
        ${recipientContent}
        ${templateContent}
        ${infraContent}`
    );
}

function communicationsSection(role = null) {
    const canWriteAnnouncement = Boolean(role?.flags?.announcementWrite);
    const canWriteReminder = Boolean(role?.flags?.reminderWrite);

    const announcementContent = canWriteAnnouncement ? `
        ${steps([
            '<b>Buka Pengumuman.</b> Pilih Buat Pengumuman atau Edit pada data yang ada.',
            '<b>Isi judul, pesan, dan lampiran opsional.</b> Bila berasal dari Pengingat, pertahankan keterkaitan yang benar.',
            '<b>Pilih channel dengan sadar.</b> Pada pembuatan baru, Email dan WhatsApp dicentang secara default. Publikasikan langsung mengantrekan blast; pada edit channel default kosong dan memilihnya berarti kirim ulang.',
            '<b>Periksa jumlah penerima valid pada tabel utama siswa/wali</b> lalu Publikasikan/Perbarui. Publikasi tidak otomatis menargetkan dataset Koperasi, YPIK, Pam Jaya, atau Umum.',
            '<b>Pantau log.</b> Tinjau total, terkirim, gagal, menunggu, dan open rate email bila tracking tersedia.',
        ])}
        ${callout('warning', 'Publikasi langsung mengirim', 'Tidak ada dialog konfirmasi tambahan sebelum proses blast. Lampiran pengumuman maksimal 2 MB tersimpan pada modul admin, tetapi saat ini tidak diteruskan ke payload blast; kirim lampiran melalui modul Pesan Massal bila memang diperlukan.')}` : `
        <p>Buka daftar Pengumuman, gunakan pencarian, lalu tinjau judul/pesan, pembuat, pengingat terkait, channel, total log, terkirim/gagal/menunggu, dan statistik pembukaan. Form/tombol kelola dapat masih terlihat pada sebagian view, tetapi endpoint mutasi menolak 403 untuk role read-only.</p>`;

    const reminderContent = canWriteReminder ? `
        ${steps([
            '<b>Buat Pengingat Baru.</b> Isi judul, deskripsi opsional, tanggal & jam, serta menit peringatan sebelum jadwal.',
            '<b>Pilih tipe.</b> Gunakan Umum atau Pengumuman; bila tipe Pengumuman, pilih pengumuman terkait atau tandai kebutuhan membuat yang baru.',
            '<b>Simpan.</b> Tanggal lampau tetap dapat diterima, jadi periksa tanggal/jam dengan teliti. Status dapat Aktif, Mendekati, Hari-H/Jatuh Tempo, atau Belum Alert.',
            '<b>Edit atau aktif/nonaktifkan</b> sesuai perubahan jadwal. Tidak ada aksi hapus; pengingat due aktif dapat terus memunculkan popup sampai dinonaktifkan.',
        ])}` : `
        <p>Buka Pengingat dan tinjau judul, jadwal, tipe, keterkaitan pengumuman, status aktif, serta indikator mendekati/jatuh tempo. Form/tombol yang mungkin terlihat bukan izin; endpoint mutasi tetap menolak role read-only.</p>`;

    return section(
        'pengumuman-dan-pengingat',
        'Pengumuman dan Pengingat',
        canWriteAnnouncement || canWriteReminder ? 'Kelola komunikasi terjadwal dan publikasi lintas channel.' : 'Tinjau komunikasi dan jadwal dalam mode read-only.',
        `<h3>Pengumuman</h3>
        ${breadcrumb(['Sidebar', 'Pesan Massal', 'Pengumuman'])}
        ${announcementContent}
        <h3>Pengingat</h3>
        ${breadcrumb(['Sidebar', 'Pengingat'])}
        ${reminderContent}
        ${callout('', 'Hubungan kedua modul', 'Pengingat adalah popup in-app, bukan pengiriman Email/WhatsApp. Browser melakukan polling sekitar setiap 60 detik; popup mendekati memiliki cooldown sekitar 10 menit dan due sekitar 2 menit. Pengingat dapat menjadi sumber pengumuman dan ditautkan saat publikasi.')}`
    );
}

function platformOperationsSection(role = null) {
    const isSystemRoot = Boolean(role?.flags?.systemRoot);

    return section(
        'operasional-platform',
        'Operasional Platform untuk Superadmin',
        'Fitur lintas modul yang hanya tersedia untuk IT Support dan/atau Sistem Management.',
        `<h3>Penerima notifikasi pemeliharaan</h3>
        ${breadcrumb(['Dasbor', 'Alat Superadmin', 'Distribusi Laporan Pemeliharaan'])}
        ${steps([
            '<b>Periksa email master.</b> Alamat ini selalu aktif dan tidak dapat dihapus dari panel.',
            '<b>Pilih Tambah Email.</b> Isi nama/keterangan PIC dan email yang valid.',
            '<b>Pilih Kelola Daftar</b> untuk meninjau atau menghapus email tambahan.',
            '<b>Uji dari laporan pemeliharaan</b> bila ada perubahan daftar, tanpa mengirim data sensitif ke alamat yang salah.',
        ])}
        <h3>Tema Website</h3>
        ${breadcrumb(['Sidebar', 'Tema Website'])}
        <ul>
            <li>Atur warna utama, pendukung, aksen, sidebar, latar, dan permukaan; gunakan preview sebelum menyimpan.</li>
            <li>Alternatifnya, unggah gambar/logo maksimal 8 MB untuk mengambil palet dominan.</li>
            <li>Tema berlaku global; mode terang/gelap tetap menjadi pilihan masing-masing pengguna.</li>
            <li>Gunakan “Kembalikan Tema Awal” bila kontras/keterbacaan bermasalah.</li>
        </ul>
        <h3>Kategori Finance</h3>
        <p>Kelola kategori berdiri sendiri/gabungan, anggota mapping, sumber, deskripsi, dan visibilitas. Perubahan mapping harus diuji pada laporan pembanding sebelum dan sesudah.</p>
        <h3>Perangkat WhatsApp dan akun Email</h3>
        <p>Gunakan prosedur pada bab Pesan Massal: verifikasi provider, perangkat aktif, antrean, kesehatan SMTP, tes kirim, limit harian, dan pengirim utama.</p>
        ${callout('danger', 'Perubahan global', `Tema, kategori, provider, perangkat, SMTP, dan daftar penerima email maintenance memengaruhi pengguna/proses lain. Catat kondisi awal dan siapkan rollback.${isSystemRoot ? ' Untuk pengendalian root yang lebih luas, gunakan bab Sistem Management.' : ''}`)}`
    );
}

function systemManagementSection() {
    return section(
        'konsol-sistem-management',
        'Konsol Sistem Management',
        'Panduan kontrol root untuk ketersediaan, akses, fitur, audit, CMS, API, AI, dan pemulihan.',
        `${breadcrumb(['Login khusus', 'Dashboard Sistem'])}
        ${callout('danger', 'Prinsip perubahan root', 'Gunakan otorisasi yang sah, catat keadaan sebelum perubahan, lakukan satu perubahan per waktu, verifikasi dengan role terdampak, dan siapkan rollback. Jangan menempelkan credential pada field non-rahasia atau dokumentasi.')}
        <h3>Status Sistem</h3>
        <ul>
            <li>Mulai dari kartu berlabel <b>Perlu Cek</b>; baca state dan detail service.</li>
            <li>Hubungkan temuan dengan waktu audit, log aplikasi, status database/cache/queue/provider, dan perubahan terakhir.</li>
            <li>Jangan menganggap kartu “normal” sebagai bukti transaksi end-to-end; lakukan uji fungsi sesuai modul.</li>
        </ul>
        <h3>Maintenance Web</h3>
        ${flow(['Catat kondisi', 'Siapkan pesan', 'Aktifkan', 'Kerjakan & pantau', 'Nonaktifkan', 'Uji semua role'])}
        ${steps([
            '<b>Masukkan pesan maintenance</b> yang jelas dan tidak membocorkan detail teknis.',
            '<b>Pilih Maintenance Aktif / Matikan Web.</b> Perubahan berlaku langsung tanpa dialog konfirmasi; role lain menerima 503 dan Sistem Management tetap dapat masuk.',
            '<b>Lakukan pekerjaan</b> sambil mempertahankan sesi pemulihan yang aman.',
            '<b>Pilih Web Normal / Nyalakan Web</b> setelah verifikasi teknis.',
            '<b>Uji login, dashboard, dan satu aksi kritis</b> dari role yang relevan.',
        ])}
        <h3>Alur Blast dan Arsip</h3>
        <ul>
            <li>Alur Blast menampilkan campaign WhatsApp/Email, status, total, sent, failed, pending, dan provider pending.</li>
            <li>Korelasikan kegagalan dengan perangkat/provider, queue worker, akun email, serta log campaign.</li>
            <li>Arsip menampilkan log blast yang dihapus dari area aktif. Gunakan sebagai bukti penelusuran, bukan sumber untuk retry otomatis.</li>
        </ul>
        <h3>Audit Akses</h3>
        <ul>
            <li>Tinjau akses web/API dan Riwayat Login menggunakan user, role, IP, browser/user-agent, route, status, serta waktu.</li>
            <li>Bedakan gagal autentikasi, 403 authorization, 422 validasi, 503 maintenance/feature, dan error aplikasi.</li>
            <li>Request yang dihentikan lebih awal oleh middleware maintenance/feature belum tentu masuk audit aplikasi; korelasikan dengan konfigurasi dan log infrastruktur.</li>
            <li>Ekspor/catat hanya data yang diperlukan; batasi distribusi karena log dapat memuat konteks pengguna.</li>
        </ul>
        <h3>Reset Password Semua Role</h3>
        ${steps([
            '<b>Pilih akun target</b> berdasarkan nama, email, dan role.',
            '<b>Masukkan password baru minimal 12 karakter</b> pada konsol ini.',
            '<b>Reset.</b> Aksi berlaku tanpa dialog konfirmasi dan tidak otomatis mencabut session/remember login pengguna yang sedang aktif.',
            '<b>Serahkan password melalui kanal aman</b>, minta pengguna menggantinya, lalu cabut sesi aktif melalui prosedur operasional bila insiden mengharuskannya.',
            '<b>Verifikasi akses</b> tanpa meminta pengguna membagikan password kembali.',
        ])}
        <h3>Restrict Role</h3>
        ${steps([
            '<b>Pilih role.</b> Sistem Management sendiri tidak dapat dibatasi.',
            '<b>Pilih permission</b> dan tentukan Akses diizinkan/tidak.',
            '<b>Simpan Restrict.</b> Nilai menjadi override terhadap baseline konfigurasi.',
            '<b>Uji menu dan endpoint</b> dengan akun role target. Ingat bahwa allowlist menu statis dapat tetap menyembunyikan item.',
            '<b>Catat alasan dan hasil</b> agar override tidak menjadi konfigurasi tersembunyi.',
        ])}
        <h3>Feature Toggle dan Akses Fitur</h3>
        ${table(
            ['Panel', 'Objek yang dikendalikan', 'Catatan'],
            [
                ['Feature Toggle Semua Role', 'Dashboard, Diskusi, Aset/Maintenance, Pengguna, Blast, Reminder, Tema, Finance', 'Waktu selesai wajib; alasan opsional maksimal 500 karakter dan sangat dianjurkan. Berlaku bagi non-Sistem Management.'],
                ['Akses Fitur', 'Ringkasan status modul inti', 'Mengarah ke Atur Maintenance; fitur nonaktif dapat ditayangkan lebih awal. Backend menolak disable langsung dari panel ini.'],
                ['Draft Feature Flag Developer AI', 'Record draft/flag pada tabel feature flags', 'Status tidak otomatis mengikat route/menu atau menjadikan fitur tersedia.'],
                ['Sistem Management', 'Konsol root', 'Locked/tetap aktif dan tidak dapat dinonaktifkan.'],
            ]
        )}
        ${steps([
            '<b>Pilih fitur pada Feature Toggle.</b> Pastikan nama/key/route tepat.',
            '<b>Tentukan batas maintenance.</b> Isi alasan (opsional, maksimal 500 karakter, tetapi dianjurkan untuk audit) dan waktu Nonaktif sampai (wajib lebih besar dari waktu WIB sekarang).',
            '<b>Nonaktifkan.</b> Role non-root kehilangan menu/route fitur; root tetap memiliki jalur pemulihan.',
            '<b>Pada tenggat, fitur otomatis tayang kembali.</b> Notifikasi root menawarkan Lanjut Maintenance (waktu baru) atau Tetap Tayangkan.',
            '<b>Verifikasi dengan akun role terdampak.</b> Sistem Management selalu bypass sehingga pengujian root saja tidak cukup.',
        ])}
        ${callout('warning', 'Pemulihan otomatis harus ditindaklanjuti', 'Jangan menganggap fitur tetap nonaktif setelah batas waktu. Jika perbaikan belum selesai, pilih Lanjut Maintenance dengan tenggat baru dan perbarui alasan bila diperlukan; bila selesai, akui Tetap Tayangkan.')}
        <h3>CMS Web</h3>
        <ul>
            <li>Atur Brand Pendek, Label Sidebar, lebar konten (default/wide/compact), banner, teks banner, dan Custom CSS.</li>
            <li>Panel CMS tidak menyediakan preview atau rollback otomatis. Uji dampak pada desktop/mobile, mode terang/gelap, tabel, modal, serta halaman login setelah menyimpan.</li>
            <li>Simpan salinan nilai/CSS sebelumnya secara aman. Sanitasi Custom CSS terbatas; hindari selector global, URL/asset tak tepercaya, atau aturan yang menyembunyikan kontrol.</li>
        </ul>
        <h3>API Tester</h3>
        ${steps([
            '<b>Pilih method</b> GET/POST/PUT/PATCH/DELETE/HEAD/OPTIONS dan URL HTTP/HTTPS yang disetujui.',
            '<b>Atur timeout 1–60 detik.</b> Isi headers sebagai JSON object.',
            '<b>Pilih body type</b> none/JSON/form URL encoded/raw dan isi body valid.',
            '<b>Kirim Request.</b> Tidak ada dialog konfirmasi; input headers/body dipertahankan pada hasil agar dapat diperiksa. Tinjau status, durasi, body, dan response headers.',
        ])}
        ${callout('danger', 'Dapat mengubah sistem eksternal maupun internal', 'Method mutasi dan URL produksi menimbulkan perubahan nyata. Private network diizinkan secara default kecuali konfigurasi membatasi. Tidak ada dialog konfirmasi. Pastikan target, payload, otorisasi, dan rollback sebelum mengirim; sensor token dari hasil/screenshot.')}
        <h3>Developer AI</h3>
        ${table(
            ['Fungsi', 'Cara aman'],
            [
                ['Draft Fitur AI', 'Isi modul dan tujuan untuk membuat record draft key/nama/deskripsi/prompt/rollout; record ini tidak otomatis mendaftarkan route/menu.'],
                ['Mode Plan', 'Gunakan untuk analisis/perencanaan tanpa permintaan apply.'],
                ['Mode Apply', 'Dapat menjalankan perubahan melalui endpoint executor bila terhubung; wajib scope sempit, review, backup, dan approval.'],
                ['Executor belum tersambung', 'Sistem menampilkan status gagal informatif; jangan menaruh endpoint/token di manual.'],
            ]
        )}
        ${callout('danger', 'AI Apply adalah aksi berprivilege tinggi', 'Instruksi harus eksplisit, target scope terbatas, perubahan dapat direview, dan rollback tersedia. Jangan gunakan untuk melewati change management.')}`
    );
}

function operationalChecklistSection(role) {
    const moduleChecks = [];
    if (role.modules.includes('asset')) moduleChecks.push('Untuk aset: catat kode aset, kategori, unit, serta sumber perubahan/impor.');
    if (role.modules.includes('maintenance')) moduleChecks.push('Untuk maintenance: catat kode aset, tanggal, status awal/akhir, dan bukti.');
    if (role.modules.includes('finance')) moduleChecks.push('Untuk finance: catat kategori, periode, nomor faktur/jurnal, nilai, dan status DRAFT/POSTED.');
    if (role.modules.includes('blasting')) moduleChecks.push('Untuk blasting: catat channel, campaign/reference, jumlah target, status, dan error tanpa menyalin credential.');
    if (role.modules.includes('user-management')) moduleChecks.push('Untuk pengguna: catat target, role sebelum/sesudah, jenis reset, dan persetujuan; jangan catat password.');
    if (role.flags.systemRoot) moduleChecks.push('Untuk perubahan root: catat kondisi awal, alasan, batas waktu, approver, verifikasi role target, dan rollback.');

    return section(
        'checklist-operasional',
        `Checklist Operasional ${esc(role.label)}`,
        'Checklist singkat agar pekerjaan dapat ditelusuri dan diserahterimakan.',
        `<h3>Rutinitas</h3>
        ${checklist(role.routine.map(esc))}
        <h3>Sebelum menyimpan, mengirim, atau menghapus</h3>
        ${checklist([
            'Role dan akun aktif sudah benar.',
            'Lingkup filter/pilihan sudah diperiksa; terpilih tidak tertukar dengan semua hasil.',
            'Data sumber dan field wajib sudah benar.',
            'Dampak ke pengguna/channel/periode lain telah dipahami.',
            'Konfirmasi pada layar dibaca, bukan langsung disetujui.',
            'Bukti/arsip tersedia untuk aksi yang sulit dibatalkan.',
        ])}
        <h3>Catatan serah terima</h3>
        <ul>${moduleChecks.map((item) => `<li>${esc(item)}</li>`).join('')}</ul>
        <p><b>Eskalasi:</b> ${esc(role.escalation)}</p>
        ${callout('warning', 'Jangan menyimpan rahasia di catatan', 'Password, App Password, token API, session, dan kunci provider tidak boleh masuk screenshot, spreadsheet serah terima, Diskusi, atau manual.')}`
    );
}

function troubleshootingSection(role) {
    const rows = [
        ['Tidak bisa login', 'Email/password salah, jalur login salah, akun/role berubah', role.flags.systemRoot ? 'Pastikan memakai <code>/system-management/login</code>; verifikasi akun dan waktu.' : 'Gunakan login standar; reset melalui pengelola akun bila diperlukan.'],
        ['Menu hilang', 'Permission/allowlist role/feature global', 'Catat nama menu, role, waktu; minta Sistem Management memeriksa baseline dan override.'],
        ['403', 'Tidak berhak pada route/aksi', 'Kembali ke menu yang tersedia dan eskalasi; jangan mencoba endpoint langsung.'],
        ['503', 'Maintenance global atau feature nonaktif', 'Periksa pengumuman/waktu pemulihan; role Sistem Management memeriksa Feature Toggle.'],
        ['419', 'Sesi/CSRF kedaluwarsa', 'Muat ulang dan login kembali; input belum tersimpan perlu diulang.'],
        ['422', 'Validasi field/file gagal', 'Baca pesan per field, koreksi format/ukuran/tanggal, lalu kirim ulang sekali.'],
        ['Data kosong setelah filter', 'Filter terlalu sempit atau kategori/periode salah', 'Reset filter, pilih scope bertahap, lalu pastikan data sumber memang ada.'],
    ];

    if (role.modules.includes('asset')) rows.push(['Impor aset gagal/hasil tidak sesuai', 'Templat/sheet/kolom/format kode salah', 'Unduh templat baru, jangan ubah header, cek sheet aktif/Data Aset, lalu uji sampel.']);
    if (role.modules.includes('maintenance') || role.modules.includes('public-maintenance')) rows.push(['Laporan QR gagal dikirim', 'Foto wajib/format/ukuran atau field belum lengkap', 'Gunakan JPG/JPEG/PNG/WEBP ≤ 5 MB dan lengkapi semua field wajib.']);
    if (role.modules.includes('finance')) rows.push(['Faktur tidak bisa diposting', 'Debit-kredit tidak seimbang atau field wajib kosong', 'Periksa setiap baris, akun, tanggal, kategori, dan total; simpan Draft untuk koreksi.']);
    if (role.modules.includes('finance')) rows.push(['Tidak bisa ubah data POSTED', 'Dokumen dikunci', 'Dengan izin update, kembalikan ke Draft lebih dulu dan catat alasannya.']);
    if (role.modules.includes('blasting')) rows.push(['WA/Email gagal/menunggu lama', 'Provider, perangkat, worker, SMTP, limit, atau data target', 'Catat reference/status/error; verifikasi infrastruktur; retry hanya kegagalan final yang jelas.']);
    if (role.modules.includes('blasting') && !role.flags.systemOperator && !role.flags.systemRoot) rows.push(['Kelola Perangkat/Kontrol Email ditolak', 'Fitur khusus IT Support/Sistem Management', 'Eskalasi status/error; jangan meminta atau menggunakan credential pengirim.']);
    if (role.flags.systemRoot) rows.push(['Masa nonaktif fitur berakhir', 'Fitur otomatis tayang kembali dan menunggu konfirmasi', 'Pilih Lanjut Maintenance dengan waktu baru atau Tetap Tayangkan; catat keputusan.']);

    return section(
        'troubleshooting',
        'Troubleshooting dan Eskalasi',
        'Mulai dari pesan yang terlihat, pertahankan bukti, dan hindari pengulangan aksi yang dapat menggandakan transaksi.',
        `${table(['Gejala', 'Kemungkinan', 'Tindakan aman'], rows)}
        <h3>Informasi minimum saat eskalasi</h3>
        ${checklist([
            'Nama halaman/menu dan role (tanpa password).',
            'Waktu kejadian dalam WIB.',
            'Langkah terakhir sebelum error.',
            'Kode status/pesan error yang tampil.',
            'ID/kode aset, nomor faktur, atau reference campaign bila relevan.',
            'Screenshot yang sudah disensor dari data pribadi/credential.',
        ])}`
    );
}

function permissionAppendixSection(role) {
    return section(
        'catatan-hak-akses',
        'Catatan Hak Akses dan Tata Kelola',
        'Cara membaca cakupan manual ketika konfigurasi aktual berubah.',
        `${table(
            ['Lapisan', 'Fungsi', 'Dampak'],
            [
                ['Role database', 'Identitas peran akun', `Role manual ini: <b>${esc(role.label)}</b>.`],
                ['Baseline permission', 'Hak default dari konfigurasi aplikasi', `${role.permissionCount} permission default untuk role ini.`],
                ['Permission override', 'Menambah atau mencabut hak per role', 'Dikelola Sistem Management; dapat membuat tampilan berbeda dari manual.'],
                ['Allowlist menu/route', 'Pembatas tambahan berbasis role', 'Permission yang ditambah tidak selalu cukup untuk menampilkan menu khusus.'],
                ['Feature access', 'On/off modul untuk semua role non-root', 'Modul dapat hilang atau merespons 503 untuk sementara.'],
                ['Maintenance global', 'Menutup web untuk non-root', 'Sistem Management tetap dapat masuk untuk pemulihan.'],
            ]
        )}
        ${callout('', 'Sumber kebenaran', 'Manual menjelaskan baseline terverifikasi dari enum role, konfigurasi permission/menu, middleware route, controller, dan view pada revisi kode yang tercantum di sampul. Jika tampilan berbeda, catat deployment dan minta pemeriksaan konfigurasi runtime.')}
        <h3>Persetujuan internal</h3>
        <div class="signature"><div>Disusun oleh</div><div>Diperiksa oleh</div><div>Disetujui oleh</div></div>`
    );
}

function wholeOverviewSection() {
    return section(
        'tentang-manual-gabungan',
        'Tentang Manual Gabungan',
        'Satu rujukan untuk memahami pembagian kerja, alur lintas role, dan prosedur seluruh modul SOY YPIK.',
        `<div class="grid-3">
            <div class="card"><div class="stat">9</div><div class="card-title">Role aktif</div><div class="muted">Sesuai enum dan seeder terbaru.</div></div>
            <div class="card"><div class="stat">40</div><div class="card-title">Permission portal</div><div class="muted">Dikelompokkan per proses bisnis.</div></div>
            <div class="card"><div class="stat">1 + 9</div><div class="card-title">Paket manual</div><div class="muted">Satu gabungan dan satu per role.</div></div>
        </div>
        <h3>Cara menggunakan dokumen</h3>
        ${steps([
            '<b>Temukan role</b> pada matriks dan profil role.',
            '<b>Baca batas akses</b> sebelum membuka bab fungsional.',
            '<b>Ikuti alur modul</b> sesuai mode tulis atau read-only yang tercantum pada profil.',
            '<b>Gunakan checklist</b> sebelum aksi berdampak besar dan saat serah terima.',
            '<b>Jika tampilan berbeda</b>, periksa override permission, allowlist role, feature access, dan maintenance runtime.',
        ])}
        ${callout('warning', 'Baseline kode', 'Dokumen ini menggambarkan akses default. Data override pada deployment aktif tidak diverifikasi karena database runtime bukan bagian dari proses pembuatan manual.')}
        ${callout('danger', 'Dokumen internal', 'Tidak memuat kredensial. Jangan menambahkan password, App Password, token gateway, token SMTP/API, atau data pribadi penerima pada salinan manual.')}`,
        'Kontrol Dokumen'
    );
}

function wholeRoleMatrixSection() {
    return section(
        'matriks-seluruh-role',
        'Matriks Seluruh Role',
        'Ringkasan baseline akses untuk menentukan pemilik pekerjaan dan jalur eskalasi.',
        `${table(
            ['Role', 'Mode', 'Cakupan utama', 'Tidak termasuk'],
            [
                ['User', 'Dasar', 'Dasbor, Diskusi, intake QR publik', 'Permission bisnis'],
                ['Admin', 'Tulis komunikasi', 'Pengumuman, Pengingat, Blast, Penerima, Templat', 'Aset, Finance, User, perangkat/SMTP'],
                ['IT Support', 'Superadmin operasional', 'Seluruh permission portal + kategori/tema/perangkat/SMTP', 'Konsol root Sistem Management'],
                ['Asset Manager', 'Tulis aset', 'CRUD aset dan proses laporan maintenance', 'User, Finance, Blast, Sistem'],
                ['Finance', 'Tulis finance', 'CRUD aset, laporan, statements, akun, faktur, depresiasi', 'Kategori finance, Tunggakan'],
                ['Pembina', 'Read-only lintas modul', 'Aset, maintenance, user, finance, komunikasi', 'Mutasi dan Riwayat Masuk'],
                ['Blasting', 'Tulis blast', 'WA, Email, Tunggakan, Penerima, Templat', 'Perangkat/SMTP dan modul lain'],
                ['QC', 'Read-only aset', 'Aset dan maintenance', 'Seluruh mutasi'],
                ['Sistem Management', 'Root', 'Seluruh portal + konsol sistem', 'Tidak dapat direstrict'],
            ]
        )}
        <h3>Landing dan jalur login</h3>
        ${table(
            ['Kelompok', 'Login', 'Halaman awal'],
            [
                ['User, Admin, IT Support, Asset Manager, Finance, Pembina, QC', '<code>/login</code>', 'Dasbor'],
                ['Blasting', '<code>/login</code>', 'Pesan Massal'],
                ['Sistem Management', '<code>/system-management/login</code>', 'Dashboard Sistem'],
            ]
        )}
        ${callout('warning', 'Tunggakan sudah berpindah', 'Tunggakan adalah bagian dari Pesan Massal/Blasting pada implementasi saat ini. Route Finance lama hanya kompatibilitas dan tetap memerlukan akses blasting.')}`,
        'Peta Akses'
    );
}

function responsibilityMatrixSection() {
    return section(
        'matriks-tanggung-jawab',
        'Matriks Tanggung Jawab dan Handoff',
        'Gunakan matriks ini untuk mengarahkan temuan ke role yang dapat menindaklanjuti.',
        `${table(
            ['Proses', 'Pelaksana utama', 'Peninjau', 'Eskalasi teknis/root'],
            [
                ['Master aset', 'Asset Manager / Finance / IT Support', 'QC / Pembina', 'Sistem Management bila akses/fitur'],
                ['Intake maintenance', 'Pelapor QR publik', 'Asset Manager / QC / Pembina', 'IT Support untuk notifikasi/teknis'],
                ['Status maintenance', 'Asset Manager / IT Support', 'QC / Pembina', 'Sistem Management untuk pemulihan akses'],
                ['Akun pengguna', 'IT Support', 'Pembina (daftar saja)', 'Sistem Management untuk root/reset khusus'],
                ['Kategori finance', 'IT Support / Sistem Management', 'Finance / Pembina', 'Sistem Management untuk mapping global'],
                ['Transaksi/laporan/faktur', 'Finance', 'Pembina', 'IT Support / Sistem Management'],
                ['Data penerima/templat', 'Admin / Blasting', 'Pembina', 'IT Support / Sistem Management'],
                ['Pengiriman campaign', 'Admin / Blasting', 'Pembina', 'IT Support untuk provider; Sistem Management untuk root'],
                ['Pengumuman/pengingat', 'Admin', 'Pembina', 'IT Support / Sistem Management'],
                ['Provider, perangkat, SMTP, tema', 'IT Support / Sistem Management', 'Pemilik proses', 'Sistem Management'],
                ['Maintenance/permission/feature/CMS/AI/API', 'Sistem Management', 'Pemilik sistem', 'Prosedur perubahan organisasi'],
            ]
        )}
        ${flow(['Temuan dicatat', 'Pemilik proses memverifikasi', 'Operator memperbaiki', 'Peninjau menguji', 'Bukti ditutup/diarsipkan'])}
        ${callout('', 'Identitas referensi', 'Gunakan kode aset, tanggal laporan, kategori/periode, nomor faktur/jurnal, atau campaign/reference. Jangan gunakan nama file/screenshot saja sebagai identitas utama.')}`,
        'Tata Kelola'
    );
}

const moduleFactories = {
    'public-maintenance': publicMaintenanceSection,
    asset: assetSection,
    maintenance: maintenanceSection,
    'user-management': userManagementSection,
    finance: financeSection,
    blasting: blastingSection,
    communications: communicationsSection,
    'platform-operations': platformOperationsSection,
    'system-management': systemManagementSection,
};

function roleSections(role) {
    const requestedModules = [...role.modules];
    if (requestedModules.includes('maintenance') && !requestedModules.includes('public-maintenance')) {
        const maintenanceIndex = requestedModules.indexOf('maintenance');
        requestedModules.splice(maintenanceIndex, 0, 'public-maintenance');
    }

    return [
        roleOverviewSection(role),
        accessSection(role),
        sharedToolsSection(role),
        ...requestedModules.map((module) => moduleFactories[module](role)),
        operationalChecklistSection(role),
        troubleshootingSection(role),
        permissionAppendixSection(role),
    ];
}

function combinedSections() {
    const allCapabilities = roles.find((role) => role.slug === 'sistem-management');

    return [
        wholeOverviewSection(),
        wholeRoleMatrixSection(),
        accessSection(),
        sharedToolsSection(),
        publicMaintenanceSection(),
        ...roles.map((role) => roleOverviewSection(role, true)),
        responsibilityMatrixSection(),
        assetSection(allCapabilities),
        maintenanceSection(allCapabilities),
        userManagementSection(allCapabilities),
        financeSection(allCapabilities),
        blastingSection(allCapabilities),
        communicationsSection(allCapabilities),
        platformOperationsSection(allCapabilities),
        systemManagementSection(),
        section(
            'checklist-lintas-role',
            'Checklist Lintas Role',
            'Pemeriksaan akhir untuk proses yang berpindah dari satu role ke role lain.',
            `${checklist([
                'Pemilik proses, pelaksana, dan peninjau telah ditentukan.',
                'ID/kode/periode/reference konsisten pada seluruh catatan.',
                'Status awal dan status akhir telah dicatat.',
                'Aksi yang sulit dibatalkan memiliki bukti/arsip.',
                'Role read-only melakukan verifikasi tanpa meminjam akun operator.',
                'Perubahan akses/fitur diuji memakai akun role terdampak.',
                'Tidak ada password, token, atau data pribadi berlebihan pada handoff.',
                'Kegagalan pengiriman/transaksi tidak diulang sebelum status final jelas.',
            ])}
            <h3>Kontrol dokumen</h3>
            ${table(
                ['Item', 'Nilai'],
                [
                    ['Versi manual', MANUAL_VERSION],
                    ['Versi aplikasi pada UI', APP_VERSION],
                    ['Tanggal penyusunan', esc(generatedDate)],
                    ['Acuan snapshot kode', `<code>${esc(codeReference)}</code>`],
                    ['Cakupan', '9 role dan seluruh modul operasional utama'],
                ]
            )}
            <div class="signature"><div>Disusun oleh</div><div>Diperiksa oleh</div><div>Disetujui oleh</div></div>`,
            'Penutupan'
        ),
    ];
}

function coverHtml({ title, subtitle, roleLabel, combined }) {
    return `<section class="cover">
        <div class="brand-row">
            <div class="brand-primary">
                <img src="${assets.ypik}" alt="Logo YPIK">
                <div><div class="brand-name">SISTEM OPERASIONAL YAYASAN YPIK</div><div class="brand-sub">SOY YPIK PAM JAYA · Versi Aplikasi ${APP_VERSION}</div></div>
            </div>
            <div class="partner-logos">
                <img src="${assets.si}" alt="Logo SI">
                <img src="${assets.pradita}" alt="Logo Pradita">
            </div>
        </div>
        <div class="cover-main">
            <span class="eyebrow">Manual Operasional Internal</span>
            <h1>${esc(title)}</h1>
            <p class="subtitle">${esc(subtitle)}</p>
            <span class="role-pill">${combined ? 'SELURUH ROLE' : `ROLE: ${esc(roleLabel)}`}</span>
        </div>
        <div>
            <dl class="cover-meta">
                <dt>Versi manual</dt><dd>${MANUAL_VERSION}</dd>
                <dt>Disusun</dt><dd>${esc(generatedDate)}</dd>
                <dt>Acuan kode</dt><dd>${esc(codeReference)}</dd>
                <dt>Cakupan</dt><dd>${combined ? '9 role dan seluruh alur operasional' : `Panduan khusus ${esc(roleLabel)}`}</dd>
                <dt>Format</dt><dd>PDF A4 · Bahasa Indonesia</dd>
            </dl>
            <div class="classification">INTERNAL — Dokumen tidak memuat kredensial. Distribusikan sesuai kebijakan Yayasan YPIK.</div>
        </div>
    </section>`;
}

function documentHtml({ title, subtitle, roleLabel = '', sections, combined = false }) {
    const toc = sections.map((item) => `<li><a href="#${esc(item.id)}">${esc(item.title.replace(/<[^>]+>/g, ''))}</a></li>`).join('');
    const bodySections = sections.map((item, index) => {
        const numbered = item.html.replace('<h2>', `<h2>${index + 1}. `);
        return `<section class="section${index === 0 ? ' first' : ''}" id="${esc(item.id)}">${numbered}<div class="doc-footer">SOY YPIK · ${esc(combined ? 'Manual Seluruh Role' : `Role ${roleLabel}`)} · v${MANUAL_VERSION} · ${esc(footerCodeReference)}</div></section>`;
    }).join('\n');

    return `<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="author" content="Yayasan YPIK">
    <meta name="description" content="${esc(subtitle)}">
    <title>${esc(title)}</title>
    <style>${styles}</style>
</head>
<body>
    ${coverHtml({ title, subtitle, roleLabel, combined })}
    <section class="toc">
        <div class="section-kicker">Navigasi Dokumen</div>
        <h2>Daftar Isi</h2>
        <p class="lead">Klik judul untuk menuju bagian terkait pada pembaca PDF yang mendukung tautan internal.</p>
        <ol class="toc-list">${toc}</ol>
        <div class="callout"><strong>Petunjuk</strong>Manual per role hanya memuat fungsi yang relevan. Manual gabungan memuat profil seluruh role dan alur fungsional lintas role.</div>
    </section>
    ${bodySections}
</body>
</html>`;
}

function findBrowser() {
    const candidates = [
        process.env.MANUAL_BROWSER,
        'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
        'C:\\Program Files (x86)\\Google\\Chrome\\Application\\chrome.exe',
        'C:\\Program Files\\Microsoft\\Edge\\Application\\msedge.exe',
        'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe',
    ].filter(Boolean);

    const browser = candidates.find((candidate) => fs.existsSync(candidate));
    if (!browser) {
        throw new Error('Chrome/Edge tidak ditemukan. Set MANUAL_BROWSER ke lokasi browser Chromium.');
    }
    return browser;
}

function validateHtml(html, title, expectedLabels) {
    const failures = [];
    if (!html.includes(`<title>${esc(title)}</title>`)) failures.push('metadata title hilang');
    if (html.includes('undefined') || html.includes('NaN')) failures.push('token undefined/NaN ditemukan');
    for (const label of expectedLabels) {
        if (!html.includes(label)) failures.push(`label wajib tidak ditemukan: ${label}`);
    }
    const secretPatterns = [
        /APP_KEY\s*=/i,
        /MAIL_PASSWORD\s*=/i,
        /(?:API_KEY|ACCESS_TOKEN|SECRET_KEY)\s*=\s*[^<\s]{8,}/i,
        /Bearer\s+[A-Za-z0-9._-]{16,}/i,
    ];
    if (secretPatterns.some((pattern) => pattern.test(html))) failures.push('pola credential terdeteksi');
    if (failures.length) throw new Error(`Preflight HTML gagal untuk ${title}: ${failures.join('; ')}`);
}

function approximatePageCount(buffer) {
    const text = buffer.toString('latin1');
    const pageObjects = text.match(/\/Type\s*\/Page\b/g);
    if (pageObjects?.length) return pageObjects.length;
    const counts = [...text.matchAll(/\/Count\s+(\d+)/g)].map((match) => Number(match[1]));
    return counts.length ? Math.max(...counts) : null;
}

function validatePdf(pdfPath) {
    const data = fs.readFileSync(pdfPath);
    const header = data.subarray(0, 8).toString('ascii');
    const tail = data.subarray(Math.max(0, data.length - 2048)).toString('latin1');
    const body = data.toString('latin1');
    if (!header.startsWith('%PDF-')) throw new Error(`Magic PDF tidak valid: ${pdfPath}`);
    if (!tail.includes('%%EOF')) throw new Error(`EOF PDF tidak ditemukan: ${pdfPath}`);
    if (data.length < 50000) throw new Error(`PDF terlalu kecil (${data.length} byte): ${pdfPath}`);
    const a4 = /\/MediaBox\s*\[\s*0\s+0\s+(?:594(?:\.\d+)?|595(?:\.\d+)?)\s+(?:841(?:\.\d+)?|842(?:\.\d+)?)\s*\]/.test(body);
    if (!a4) throw new Error(`MediaBox A4 tidak ditemukan: ${pdfPath}`);
    return { bytes: data.length, pages: approximatePageCount(data), sha256: crypto.createHash('sha256').update(data).digest('hex') };
}

function printPdf(browser, htmlPath, pdfPath) {
    if (fs.existsSync(pdfPath)) fs.rmSync(pdfPath, { force: true });
    const profileDir = fs.mkdtempSync(path.join(os.tmpdir(), 'soy-ypik-manual-'));
    try {
        const result = spawnSync(browser, [
            '--headless=new',
            '--disable-gpu',
            '--disable-extensions',
            '--disable-features=Translate',
            '--allow-file-access-from-files',
            '--run-all-compositor-stages-before-draw',
            '--virtual-time-budget=1500',
            '--no-pdf-header-footer',
            `--user-data-dir=${profileDir}`,
            `--print-to-pdf=${pdfPath}`,
            pathToFileURL(htmlPath).href,
        ], {
            cwd: projectRoot,
            encoding: 'utf8',
            windowsHide: true,
            timeout: 120000,
        });

        if (result.status !== 0 || !fs.existsSync(pdfPath)) {
            throw new Error(`Browser gagal mencetak ${path.basename(pdfPath)}: ${result.stderr || result.stdout || `exit ${result.status}`}`);
        }
    } finally {
        fs.rmSync(profileDir, { recursive: true, force: true });
    }
}

function writeManual(spec) {
    const html = documentHtml(spec);
    validateHtml(html, spec.title, spec.expectedLabels);
    const htmlPath = path.join(sourceDir, `${spec.filename}.html`);
    const pdfPath = path.join(outputDir, `${spec.filename}.pdf`);
    fs.writeFileSync(htmlPath, html, 'utf8');
    return { ...spec, htmlPath, pdfPath };
}

function main() {
    fs.mkdirSync(sourceDir, { recursive: true });

    const manuals = roles.map((role) => writeManual({
        filename: `manual-book-role-${role.slug}-soy-ypik`,
        title: `Manual Book Role ${role.label}`,
        subtitle: `${role.mission} Panduan ini mengikuti hak akses default dan alur UI SOY YPIK.`,
        roleLabel: role.label,
        sections: roleSections(role),
        combined: false,
        expectedLabels: [role.label, 'Masuk, Navigasi, dan Keluar', 'Troubleshooting dan Eskalasi'],
    }));

    manuals.push(writeManual({
        filename: 'manual-book-seluruh-role-soy-ypik',
        title: 'Manual Book Seluruh Role SOY YPIK',
        subtitle: 'Panduan terpadu seluruh role, pembagian tanggung jawab, alur operasional, kontrol akses, dan troubleshooting.',
        roleLabel: 'Seluruh Role',
        sections: combinedSections(),
        combined: true,
        expectedLabels: roles.map((role) => role.label),
    }));

    const browser = findBrowser();
    const manifest = [];
    for (const manual of manuals) {
        printPdf(browser, manual.htmlPath, manual.pdfPath);
        const pdf = validatePdf(manual.pdfPath);
        manifest.push({
            title: manual.title,
            role: manual.combined ? 'Seluruh Role' : manual.roleLabel,
            html: path.relative(outputDir, manual.htmlPath).replaceAll('\\', '/'),
            pdf: path.basename(manual.pdfPath),
            bytes: pdf.bytes,
            pages: pdf.pages,
            sha256: pdf.sha256,
        });
        process.stdout.write(`OK  ${path.basename(manual.pdfPath)}  ${pdf.pages ?? '?'} halaman  ${pdf.bytes} byte\n`);
    }

    const htmlFiles = fs.readdirSync(sourceDir).filter((file) => file.endsWith('.html'));
    const pdfFiles = fs.readdirSync(outputDir).filter((file) => file.endsWith('.pdf'));
    if (htmlFiles.length !== 10 || pdfFiles.length !== 10) {
        throw new Error(`Jumlah output tidak sesuai: ${htmlFiles.length} HTML dan ${pdfFiles.length} PDF (diharapkan 10/10).`);
    }

    fs.writeFileSync(path.join(outputDir, 'manual-book-manifest.json'), `${JSON.stringify({
        manual_version: MANUAL_VERSION,
        app_version: APP_VERSION,
        generated_at: new Date().toISOString(),
        generated_date_wib: generatedDate,
        git_branch: gitBranch,
        git_revision: gitRevision,
        working_tree_dirty: workingTreeDirty,
        code_reference: codeReference,
        browser,
        manuals: manifest,
    }, null, 2)}\n`, 'utf8');

    process.stdout.write(`\nSelesai: ${manifest.length} manual PDF dibuat di ${outputDir}\n`);
}

main();
