# SOY YPIK

Sistem Operasional Yayasan YPIK adalah aplikasi internal berbasis Laravel untuk
mengelola aset, laporan keuangan, maintenance, user management, diskusi
internal, dan blasting informasi.

README ini dipakai sebagai pintu masuk developer baru. Dokumentasi detail ada di
folder [docs](docs).

## Ringkasan Modul

- Asset Management: registrasi aset, edit aset, QR code, import CSV, detail aset publik.
- Finance: dashboard, kalkulator penyusutan, snapshot laba rugi, neraca, invoice, general ledger.
- Maintenance Report: pelaporan dan approval maintenance aset.
- User Management: user database, histori login, reset password.
- Discussion: channel diskusi internal dengan attachment, voice note, dan pin pesan.
- Blasting: pengiriman email dan WhatsApp, termasuk reminder dan dataset tunggakan.

## Status Fitur Penyusutan

Saat ini project sudah memiliki:

- master data aset,
- halaman kalkulator penyusutan metode garis lurus,
- tabel histori penyusutan dan run penyusutan,
- snapshot laporan finance yang bisa menandai baris sebagai penyusutan.

Namun alur tersebut masih belum otomatis dari input aset sampai generate penyusutan
akhir periode. Detail gap dan revisi requirement client ada di
[docs/finance-asset-depreciation.md](docs/finance-asset-depreciation.md).

## Quick Start

1. Install dependency PHP.

```bash
composer install
```

2. Siapkan environment.

```bash
copy .env.example .env
php artisan key:generate
```

3. Sesuaikan koneksi database di `.env`, lalu jalankan migrasi.

```bash
php artisan migrate
```

4. Jalankan aplikasi.

```bash
php artisan serve
```

## Struktur Folder Penting

- `app/Http/Controllers`: entry point request web.
- `app/Services`: business logic utama.
- `app/Repositories`: query layer yang dipakai service tertentu.
- `app/DTOs`: payload terstruktur antar layer.
- `app/Models`: model Eloquent.
- `resources/views`: Blade UI.
- `routes/web.php`: routing utama.
- `database/migrations`: struktur database.
- `whatsapp-gateway/`: service Node.js terpisah untuk WhatsApp.

## Entry Point Yang Paling Sering Dibuka

- Asset master: [app/Http/Controllers/Asset/AssetManagementController.php](app/Http/Controllers/Asset/AssetManagementController.php)
- Asset service: [app/Services/Asset/AssetService.php](app/Services/Asset/AssetService.php)
- Finance depreciation: [app/Http/Controllers/Finance/AssetDepreciationController.php](app/Http/Controllers/Finance/AssetDepreciationController.php)
- Finance report: [app/Services/Finance/ReportService.php](app/Services/Finance/ReportService.php)
- Routing: [routes/web.php](routes/web.php)

## Dokumentasi Tambahan

- [docs/project-overview.md](docs/project-overview.md)
- [docs/finance-asset-depreciation.md](docs/finance-asset-depreciation.md)

Dokumen lama masih ada untuk referensi historis:

- `documentaion.txt`
- `dokumentasi-teknis.txt`

## Catatan Untuk Developer Berikutnya

- Asset registration saat ini fokus ke master data inventaris, belum menyimpan policy penyusutan finance.
- Halaman penyusutan saat ini adalah kalkulator manual plus log, belum generator penyusutan otomatis per akhir periode.
- Snapshot laba rugi masih menerima input penyusutan manual dari form.
- Jika ingin menyelesaikan kebutuhan client tentang otomatisasi penyusutan, mulai dari dokumen
  [docs/finance-asset-depreciation.md](docs/finance-asset-depreciation.md).
