# Analisis Akses Role dan Bahan Use Case

Dokumen ini merangkum akses setiap role berdasarkan sumber kebenaran di proyek:

- `config/role_permission.php`
- `app/Enums/User/UserRole.php`
- `app/Enums/Portal/PortalPermission.php`
- `routes/web.php`
- `app/Http/Middleware/EnsureFinanceAccess.php`
- `app/Http/Controllers/Admin/BlastController.php`
- `app/Http/Controllers/DiscussionController.php`

## 1. Aktor Sistem

### Aktor internal

- `User`
- `Admin`
- `IT Support`
- `Asset Manager`
- `Finance`
- `Pembina`
- `Blasting`
- `QC`

### Aktor eksternal / non-role

- `Guest/Public`
- `Teknisi/Pekerja lapangan`

Catatan:

- `Teknisi/Pekerja lapangan` tidak harus login, karena form submit maintenance dapat diakses dari halaman detail aset publik.
- `Guest/Public` juga bisa membuka detail aset publik melalui route `assets/{id}`.

## 2. Akses Bersama

### Akses untuk semua user yang login

- Login dan logout
- Dashboard
- Discussion
- Kirim pesan diskusi
- Upload attachment / voice note di diskusi
- Hapus pesan diskusi milik sendiri

### Akses publik tanpa login

- Lihat detail aset publik
- Submit maintenance report dari halaman detail aset
- Reset password melalui token

## 3. Matriks Akses per Role

| Role | Modul utama yang bisa diakses | Level akses |
| --- | --- | --- |
| `User` | Dashboard, Discussion | Akses umum internal saja |
| `Admin` | Announcement, Reminder, Blast, Recipient, Template Blast | Operasional komunikasi |
| `IT Support` | Semua modul | Superadmin |
| `Asset Manager` | Asset Management, Maintenance Report | Kelola aset dan tindak lanjut maintenance |
| `Finance` | Finance Dashboard, Depreciation, Report, Statement, Invoice, Tunggakan | Operasional keuangan penuh |
| `Pembina` | Asset, Maintenance, User Management, Finance, Announcement, Reminder, Blast, Recipient, Template | Read-only lintas modul |
| `Blasting` | Blast, Recipient, Template Blast | Operasional blasting saja |
| `QC` | Asset Management, Maintenance Report | Read-only aset dan maintenance |

## 4. Detail Akses per Role

### `Guest/Public`

Hak akses:

- Melihat detail aset publik
- Mengirim laporan maintenance melalui form publik
- Mengakses halaman reset password berbasis token

Kandidat use case:

- Lihat detail aset
- Kirim laporan maintenance aset
- Reset password

### `User`

Hak akses:

- Akses dashboard
- Akses discussion
- Mengirim pesan diskusi
- Menghapus pesan diskusi milik sendiri

Tidak punya permission bisnis khusus untuk modul:

- Asset Management
- Maintenance Report internal
- User Management
- Finance
- Announcement
- Reminder
- Blast

Kandidat use case:

- Lihat dashboard
- Diskusi antar pengguna

### `Admin`

Hak akses komunikasi:

- `Announcement`
  - lihat daftar
  - buat
  - edit
  - hapus
- `Reminder`
  - lihat daftar
  - buat / kirim
  - edit
  - toggle aktif/nonaktif
- `Blast`
  - lihat dashboard blast
  - kirim WhatsApp blast
  - kirim email blast
  - lihat activity log
  - clear log
  - retry log
  - pause / resume / stop campaign
- `Recipient`
  - lihat data recipient siswa
  - tambah
  - edit
  - import
  - hapus
  - bulk delete
  - kelola recipient karyawan
  - kelola recipient karyawan YPIK
  - kelola recipient YPIK Pam Jaya
- `Template Blast`
  - lihat
  - tambah
  - edit
  - hapus

Hak akses tambahan:

- Dashboard
- Discussion
- Monitoring status gateway WhatsApp non-sensitif

Tidak punya akses:

- Asset Management
- Maintenance Report internal
- User Management
- Finance
- Manage Phone WhatsApp khusus IT Support

Kandidat use case:

- Kelola pengumuman
- Kelola reminder
- Blast pesan massal
- Kelola recipient blast
- Kelola template blast

### `IT Support`

Hak akses:

- Semua permission pada sistem
- Semua modul bisnis yang dimiliki role lain
- User Management penuh:
  - lihat user
  - tambah user
  - edit user 
  - hapus user
  - kirim link reset password
- Manage Phone WhatsApp:
  - buka halaman manage phone
  - cek provider
  - ganti provider `gateway` / `wablas`
  - reconnect gateway
  - lihat daftar device lengkap
  - buat device
  - connect device
  - activate device
  - reconnect device
  - disconnect device
  - rename device
  - reset semua device
  - hapus device

Kandidat use case:

- Kelola user dan role
- Kelola seluruh modul sistem
- Kelola device WhatsApp gateway
- Troubleshooting integrasi blasting

### `Asset Manager`

Hak akses:

- `Asset Management`
  - lihat daftar aset
  - register aset
  - edit aset
  - hapus aset
  - bulk delete aset
  - download QR code aset
- `Maintenance Report`
  - lihat daftar
  - lihat detail
  - export Excel
  - edit laporan
  - update status laporan
  - hapus laporan

Hak akses tambahan:

- Dashboard
- Discussion

Tidak punya akses:

- User Management
- Finance
- Blast komunikasi

Kandidat use case:

- Kelola data aset
- Tindak lanjuti laporan maintenance
- Ubah status maintenance
- Hapus laporan maintenance yang tidak valid

### `Finance`

Hak akses:

- `Finance Dashboard`
  - lihat dashboard finance
- `Depreciation`
  - lihat halaman depresiasi
  - hitung depresiasi
  - lihat log depresiasi
  - download log depresiasi
- `Finance Report`
  - lihat input report
  - generate report
  - edit report
  - hapus report
  - lihat snapshots
  - lihat detail report
  - download dokumen report
- `Financial Statements`
  - lihat lembar saldo
  - download lembar saldo
  - lihat laba rugi
  - download laba rugi
  - lihat buku besar
  - download buku besar
  - lihat journal items
  - download journal items
- `Finance Accounts`
  - lihat bagan akun
  - tambah akun
  - edit akun
  - hapus klasifikasi akun
- `Invoice`
  - lihat invoice
  - buat invoice
  - edit invoice
  - hapus invoice draft
  - post invoice
  - set draft
  - publish all draft
  - tambah catatan invoice
  - download invoice
- `Tunggakan`
  - lihat data tunggakan
  - tambah manual
  - import Excel
  - sinkron dari database recipient
  - generate template default tunggakan
  - blast WhatsApp tunggakan
  - edit data tunggakan
  - hapus satu data
  - hapus semua data

Hak akses tambahan:

- Dashboard
- Discussion

Tidak punya akses:

- User Management
- Announcement / Reminder / Blast umum
- Manage Phone khusus IT Support

Kandidat use case:

- Kelola siklus laporan keuangan
- Kelola invoice dan posting jurnal
- Hitung depresiasi aset
- Kelola tunggakan dan blast tunggakan

### `Pembina`

Hak akses read-only:

- Asset Management baca saja
- Maintenance Report baca saja
- User Management baca saja
- Finance baca saja
- Finance statements baca saja
- Invoice baca saja
- Announcement baca saja
- Reminder baca saja
- Blast baca saja
- Recipient baca saja
- Template blast baca saja

Hak akses tambahan:

- Dashboard
- Discussion

Pembatas khusus:

- Masuk ke route finance diperbolehkan karena `Pembina` lolos middleware `ensure_finance_access`
- Namun tetap read-only karena permission create/update/delete tidak diberikan

Kandidat use case:

- Monitoring aset
- Monitoring maintenance
- Monitoring keuangan
- Monitoring aktivitas komunikasi
- Monitoring user database

### `Blasting`

Hak akses:

- `Blast`
  - lihat dashboard blast
  - kirim WhatsApp blast
  - kirim email blast
  - lihat activity log
  - clear log
  - retry log
  - pause / resume / stop campaign
- `Recipient`
  - lihat
  - tambah
  - edit
  - import
  - hapus
  - bulk delete
  - kelola recipient karyawan
  - kelola recipient karyawan YPIK
  - kelola recipient YPIK Pam Jaya
- `Template Blast`
  - lihat
  - tambah
  - edit
  - hapus

Hak akses tambahan:

- Dashboard dan Discussion tetap bisa diakses karena route-nya hanya butuh `auth`
- Root `/` diarahkan ke halaman blast
- Sidebar dibatasi hanya menampilkan menu blast

Tidak punya akses:

- Announcement
- Reminder
- Asset
- Finance
- User Management
- Manage Phone khusus IT Support

Kandidat use case:

- Kelola blasting harian
- Kelola data recipient
- Kelola template pesan

### `QC`

Hak akses:

- Asset Management baca saja
- Maintenance Report baca saja

Hak akses tambahan:

- Dashboard
- Discussion

Tidak punya akses:

- Create/update/delete aset
- Update status maintenance
- User Management
- Finance
- Blast

Kandidat use case:

- Audit aset
- Review laporan maintenance

## 5. Catatan Penting untuk Penulisan Use Case

### 1. Modul finance punya pagar role tambahan

Route finance tidak cukup hanya pakai permission. Middleware `ensure_finance_access` membatasi hanya:

- `Finance`
- `IT Support`
- `Pembina`

Artinya:

- `Admin` dan `Blasting` tidak bisa masuk modul finance walaupun suatu saat ada permission finance yang ditambahkan manual

### 2. `Manage Phone` benar-benar khusus `IT Support`

Walau route manage phone berada di dalam grup blast, controller membatasi halaman dan aksi device/provider hanya untuk `IT Support`.

Gunakan aktor terpisah untuk:

- `Operator Blast`
- `IT Support / Administrator Sistem`

### 3. `Blasting` bukan pemilik use case tunggakan

Blast tunggakan berada di modul finance dan dijaga permission `finance_report.generate`.

Artinya:

- use case `Blast tunggakan` milik aktor `Finance`
- use case `Blast pesan umum` milik aktor `Admin` atau `Blasting`

### 4. Submit maintenance adalah alur publik

Pembuatan maintenance report tidak dilakukan lewat permission internal `maintenance_report.create`.

Alurnya:

- user publik / teknisi buka detail aset publik
- kirim form maintenance
- `Asset Manager` atau role internal terkait meninjau hasilnya

Jadi untuk diagram use case, lebih tepat dipisah menjadi:

- `Teknisi/Pekerja Lapangan -> Kirim laporan maintenance`
- `Asset Manager -> Verifikasi / update / ubah status / hapus laporan maintenance`

### 5. Discussion terbuka untuk semua user login

Semua user yang login dapat:

- melihat channel diskusi
- kirim pesan
- pin pesan

Tetapi:

- hapus pesan hanya untuk pesan milik sendiri

Kalau dipakai di use case, actor internal umum bisa digabung sebagai:

- `Pengguna Internal`

### 6. `Pembina` cocok sebagai aktor monitoring

Role `Pembina` hampir seluruhnya bersifat baca saja, sehingga sangat cocok diposisikan sebagai:

- aktor monitoring
- aktor review
- aktor eksekutif / pengawas

## 6. Rekomendasi Aktor untuk Diagram Use Case

Jika ingin diagram yang rapi, gunakan aktor berikut:

- `Guest/Public`
- `Teknisi/Pekerja Lapangan`
- `Pengguna Internal`
- `Admin Komunikasi`
- `Operator Blasting`
- `Asset Manager`
- `Finance`
- `QC`
- `Pembina`
- `IT Support`

## 7. Rekomendasi Kelompok Use Case

### Kelompok umum

- Login
- Logout
- Diskusi internal
- Reset password

### Kelompok aset

- Lihat detail aset publik
- Submit maintenance report
- Kelola aset
- Review maintenance report
- Update status maintenance

### Kelompok komunikasi

- Kelola announcement
- Kelola reminder
- Blast WhatsApp
- Blast email
- Kelola recipient
- Kelola template blast
- Monitor campaign dan activity log

### Kelompok keuangan

- Hitung depresiasi
- Generate laporan keuangan
- Lihat lembar saldo
- Lihat laba rugi
- Lihat buku besar
- Kelola invoice
- Kelola tunggakan
- Blast tunggakan

### Kelompok administrasi sistem

- Kelola user
- Kelola role user
- Kelola provider WhatsApp
- Kelola device WhatsApp gateway

## 8. Revisi Diagram

Saya sudah siapkan versi revisi diagram use case yang menyesuaikan implementasi repo saat ini pada file:

- `diagram-usecase-revisi.puml`

Fokus revisinya:

- mengganti aktor full-access menjadi `IT Support`
- menambahkan aktor `Blasting`
- menghapus dependensi `Parent User` dari diagram implementasi aktif
- menambahkan aktor `Guest / Teknisi` untuk detail aset publik dan submit maintenance
- memisahkan monitoring gateway WhatsApp dengan pengelolaan provider/device
- memindahkan `Blast WhatsApp Tunggakan` ke aktor `Finance`
