# Project Overview

## Tujuan Project

SOY YPIK dipakai sebagai sistem operasional internal yayasan. Aplikasi ini
menggabungkan kebutuhan inventaris, maintenance, keuangan, user management,
komunikasi internal, dan blasting dalam satu codebase Laravel.

## Peta Modul

- Asset Management
  - Registrasi aset per kategori
  - Import CSV
  - Edit data aset
  - Generate dan download QR code
  - Halaman detail aset publik
- Maintenance Report
  - Form pelaporan maintenance
  - Approval dan update status
  - Riwayat maintenance per aset
- Finance
  - Dashboard finance
  - Kalkulator penyusutan aset
  - Snapshot laba rugi
  - Neraca
  - Invoice
  - General ledger
- User Management
  - CRUD user
  - Login history
  - Reset password
- Discussion
  - Pesan internal, attachment, voice note, pinned messages
- Blasting and Reminder
  - Email blast
  - WhatsApp blast
  - Reminder
  - Dataset tunggakan

## Alur Kode Yang Umum

1. Route di `routes/web.php` menerima request.
2. Controller memvalidasi dan menyiapkan DTO.
3. Service menjalankan business rule.
4. Repository atau model menyimpan / mengambil data.
5. Blade di `resources/views` merender UI.

## Folder Yang Paling Penting

- `app/Http/Controllers`
  - Koordinator request per modul.
- `app/Http/Requests`
  - Validation layer.
- `app/Services`
  - Business logic utama. Banyak keputusan bisnis ada di sini.
- `app/Repositories`
  - Query data yang dipakai service tertentu.
- `app/Models`
  - Relasi Eloquent dan cast model.
- `resources/views`
  - UI server-rendered dengan Blade.
- `database/migrations`
  - Sumber kebenaran struktur tabel.
- `config`
  - Menu, permission, blast config, dan setting lain.

## Hotspot Maintenance

Jika ada perubahan di area tertentu, biasanya mulai dari file ini:

- Asset registration and CRUD
  - `app/Http/Controllers/Asset/AssetManagementController.php`
  - `app/Services/Asset/AssetService.php`
  - `app/Models/Asset/Asset.php`
  - `resources/views/asset-management/*`
- Depreciation and finance reports
  - `app/Http/Controllers/Finance/AssetDepreciationController.php`
  - `app/Services/Finance/DepreciationService.php`
  - `app/Services/Finance/ReportService.php`
  - `resources/views/finance/*`
  - `database/migrations/2026_02_15_*`
- Access and menu
  - `config/menu.php`
  - `config/role_permission.php`
  - middleware `check_access` dan `ensure_finance_access`

## Catatan Arsitektur Finance Aset

- Tabel `assets` saat ini menyimpan identitas master aset.
- Tabel `finance_asset_policies`, `finance_depreciation_runs`, dan
  `finance_depreciation_histories` menunjukkan fondasi untuk otomatisasi
  penyusutan sudah disiapkan di database.
- UI dan service yang benar-benar memakai policy tersebut masih belum lengkap.
- Halaman `finance/depreciation` yang aktif sekarang masih berfungsi sebagai
  kalkulator manual metode garis lurus dan penyimpan log hasil hitung.
- Laporan laba rugi masih mengandalkan input manual untuk item penyusutan.

## Saran Onboarding Developer

1. Baca `README.md`.
2. Baca `docs/finance-asset-depreciation.md` bila menyentuh modul aset / finance.
3. Lihat `routes/web.php` untuk menemukan entry point request.
4. Ikuti alur controller -> request -> service -> repository/model -> view.
5. Cek migration sebelum mengubah model atau flow finance supaya tidak salah asumsi.
