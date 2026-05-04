# Finance Asset Depreciation

## Revisi Maksud Client

Pesan client:

- Data aset saat ini dicatat di Odoo.
- Saat akhir tahun, penyusutan masih dihitung manual di Excel.
- Client ingin pembelian aset yang diinput ke sistem bisa langsung dipakai untuk
  menghitung penyusutan pada akhir tahun.
- Contoh akun yang disebut client: "inventaris tk sd yayasan, sarana prasarana".

Interpretasi requirement yang lebih jelas:

1. Sistem harus menjadi sumber data aset yang siap dipakai untuk penyusutan, bukan
   hanya daftar inventaris.
2. Saat aset dibeli dan diinput, sistem perlu menyimpan data finance yang relevan
   untuk penyusutan, misalnya:
   - akun / kelompok aset,
   - nilai perolehan,
   - tanggal mulai penyusutan,
   - umur manfaat,
   - nilai residu bila dipakai,
   - metode penyusutan.
3. Pada akhir periode (minimal akhir tahun, dan idealnya bisa per bulan juga),
   sistem harus bisa menghasilkan nilai penyusutan otomatis tanpa ekspor manual ke Excel.
4. Hasil penyusutan harus bisa ditelusuri ulang per aset, per periode, dan per run.
5. Jika laporan laba rugi membutuhkan nilai penyusutan, nilai tersebut idealnya
   diambil dari hasil generate sistem, bukan diinput manual lagi.

## Kondisi Kode Saat Ini

### 1. Master aset belum menyimpan policy penyusutan

Flow registrasi aset saat ini berada di:

- `app/Http/Controllers/Asset/AssetManagementController.php`
- `app/Services/Asset/AssetService.php`
- `app/Models/Asset/Asset.php`
- `resources/views/asset-management/register-form.blade.php`

Data utama yang disimpan masih fokus ke identitas inventaris:

- kategori,
- account code,
- serial number,
- unit,
- lokasi,
- tahun pembelian,
- detail kategori aset.

Field finance penting untuk otomatisasi penyusutan belum masuk ke flow input aset.

### 2. Halaman penyusutan yang aktif masih kalkulator manual

Flow saat ini:

- route: `finance.depreciation.index` dan `finance.depreciation.calc`
- controller: `app/Http/Controllers/Finance/AssetDepreciationController.php`
- service: `app/Services/Finance/DepreciationService.php`
- view: `resources/views/finance/depreciation.blade.php`

Perhitungan yang dipakai:

- user memilih aset,
- user tetap memasukkan manual `acquisition_cost`,
- user tetap memasukkan manual `useful_life_months`,
- sistem menghitung `acquisition_cost / useful_life_months`,
- hasilnya disimpan sebagai log kalkulasi.

Artinya, halaman ini belum membaca policy penyusutan dari master aset.

### 3. Laporan laba rugi masih menerima penyusutan manual

Flow terkait:

- `resources/views/finance/report.blade.php`
- `app/Services/Finance/ReportService.php`

Saat ini item penyusutan di laporan ditandai dengan checkbox `is_depreciation`.
Nilainya tetap datang dari input manual form, belum auto-generated dari histori penyusutan aset.

### 4. Fondasi database untuk otomatisasi sebenarnya sudah ada

Migration yang relevan:

- `2026_02_15_130100_create_finance_asset_policies_table.php`
- `2026_02_15_130200_create_finance_depreciation_runs_table.php`
- `2026_02_15_130300_create_finance_depreciation_histories_table.php`

Maknanya:

- sistem sudah punya arah desain untuk menyimpan policy aset,
- sistem sudah punya konsep run penyusutan per periode,
- sistem sudah punya histori penyusutan per aset per run.

Tetapi UI dan service yang mengisi tabel itu belum lengkap / belum menjadi alur utama.

## Gap Antara Kebutuhan Client dan Sistem Sekarang

- Client ingin sekali input aset lalu penyusutan bisa otomatis.
- Sistem sekarang masih memisahkan input aset dan input angka penyusutan.
- Client ingin mengurangi ketergantungan pada Excel.
- Sistem sekarang masih memerlukan input manual yang pada praktiknya setara dengan
  langkah Excel yang dipindah ke web.
- Client butuh hasil akhir periode yang siap dipakai untuk laporan.
- Sistem sekarang baru menghasilkan log kalkulasi, belum run otomatis yang terhubung
  penuh ke laporan finance.

## Rekomendasi Implementasi

### Tahap 1 - Lengkapi master data aset

Tambahkan policy penyusutan saat input / edit aset:

- acquisition_cost
- residual_value
- useful_life_months
- depreciation_start_date
- depreciation_method
- mapping akun / kelompok aset

Policy tersebut sebaiknya disimpan di `finance_asset_policies`.

### Tahap 2 - Buat generator penyusutan per periode

Buat service khusus yang:

- mengambil aset aktif yang punya policy,
- menentukan aset mana yang harus disusutkan pada periode tertentu,
- menghitung nilai penyusutan,
- menyimpan hasil ke `finance_depreciation_runs` dan `finance_depreciation_histories`,
- mencegah double posting untuk aset dan periode yang sama.

### Tahap 3 - Hubungkan ke laporan finance

Saat snapshot laba rugi dibuat:

- tarik total penyusutan dari run periode terkait,
- tampilkan detail penyusutan per aset / per akun,
- kurangi kebutuhan centang manual `is_depreciation`.

### Tahap 4 - Siapkan export dan audit trail

Tambahkan:

- export Excel / PDF hasil penyusutan,
- filter per tahun / bulan / akun / unit,
- status draft / posted / void,
- catatan siapa generate dan kapan.

## Pertanyaan Yang Masih Perlu Konfirmasi Ke Client

Beberapa hal penting belum tegas dari chat client:

1. Penyusutan hanya dibutuhkan saat akhir tahun, atau juga per bulan?
2. Metode yang dipakai selalu garis lurus, atau ada metode lain?
3. Apakah setiap kelompok akun punya umur manfaat default?
4. Apakah data aset lama dari Odoo perlu diimport massal?
5. Apakah hasil penyusutan harus otomatis masuk ke laporan laba rugi dan neraca?
6. Apakah Excel tetap dibutuhkan sebagai output, atau targetnya benar-benar tanpa Excel?

## File Yang Perlu Dicek Jika Melanjutkan Fitur Ini

- `app/Http/Controllers/Asset/AssetManagementController.php`
- `app/Services/Asset/AssetService.php`
- `app/Models/Asset/Asset.php`
- `app/Http/Controllers/Finance/AssetDepreciationController.php`
- `app/Services/Finance/DepreciationService.php`
- `app/Services/Finance/ReportService.php`
- `resources/views/asset-management/register-form.blade.php`
- `resources/views/finance/depreciation.blade.php`
- `resources/views/finance/report.blade.php`
- `database/migrations/2026_02_15_130100_create_finance_asset_policies_table.php`
- `database/migrations/2026_02_15_130200_create_finance_depreciation_runs_table.php`
- `database/migrations/2026_02_15_130300_create_finance_depreciation_histories_table.php`
