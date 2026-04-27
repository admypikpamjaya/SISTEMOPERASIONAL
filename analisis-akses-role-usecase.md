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

## 9. Kekurangan Aplikasi dan Saran Perbaikan

Berikut beberapa kekurangan yang terlihat dari implementasi aplikasi saat ini beserta saran perbaikannya.

### 1. Detail aset publik terlalu terbuka

Kekurangan:

- Halaman detail aset publik memperlihatkan data yang cukup sensitif seperti kode akun, nomor serial, lokasi aset, dan riwayat maintenance.

Dampak:

- Informasi aset internal berisiko tersebar ke pihak yang tidak berkepentingan jika link halaman publik dibagikan atau ditebak.

Saran:

- Gunakan signed URL atau token QR yang memiliki masa berlaku.
- Pisahkan tampilan publik dan tampilan internal.
- Batasi data publik hanya pada informasi yang benar-benar dibutuhkan teknisi lapangan.

### 2. Form maintenance publik belum cukup aman dan belum ramah pengguna lapangan

Kekurangan:

- Form maintenance dapat dikirim tanpa login dari halaman publik.
- Validasi form masih cukup berat karena mewajibkan biaya dan foto bukti.
- Mekanisme pencegahan spam atau abuse belum terlihat kuat.

Dampak:

- Form berpotensi disalahgunakan untuk spam.
- Teknisi lapangan bisa kesulitan jika biaya belum diketahui saat awal pelaporan atau jika koneksi internet kurang stabil saat upload foto.

Saran:

- Tambahkan rate limiting, captcha, atau proteksi submit berulang.
- Jadikan `cost` opsional saat submit awal, lalu dilengkapi saat proses review.
- Pertimbangkan dukungan multi-foto dan alur submit yang lebih ringan untuk perangkat mobile.

### 3. Navigasi sistem mulai terlalu padat

Kekurangan:

- Aplikasi menggabungkan banyak modul dalam satu sidebar, seperti aset, maintenance, finance, discussion, reminder, announcement, dan blast.
- Struktur menu berpotensi membingungkan untuk role yang hanya memakai sebagian kecil fitur.

Dampak:

- Pengguna baru membutuhkan waktu lebih lama untuk memahami alur kerja.
- Risiko salah klik atau salah masuk modul menjadi lebih tinggi, terutama untuk role non-teknis.

Saran:

- Buat landing page atau dashboard khusus per role.
- Tampilkan quick action sesuai kebutuhan role masing-masing.
- Sederhanakan sidebar dengan menyembunyikan menu lanjutan ke halaman modul terkait.

### 4. Pengalaman penggunaan tabel dan aksi masih kurang ramah mobile

Kekurangan:

- Banyak halaman utama masih bertumpu pada tabel yang padat.
- Sebagian aksi ditampilkan hanya dengan ikon tanpa label yang jelas.
- Tampilan bulk action dan filter masih lebih cocok untuk desktop daripada penggunaan lapangan.

Dampak:

- Pengguna mobile akan lebih sulit membaca data dan memahami fungsi tombol.
- Aksesibilitas antarmuka menjadi kurang baik untuk pengguna non-teknis.

Saran:

- Tambahkan mode responsif atau card view untuk layar kecil.
- Beri label teks atau tooltip yang lebih jelas pada tombol aksi.
- Rapikan area filter dan bulk action agar lebih mudah dipahami saat pertama kali digunakan.

### 5. Kompleksitas implementasi mulai tinggi dan berisiko menyulitkan pengembangan

Kekurangan:

- Beberapa file view dan route sudah cukup besar dan memuat banyak logika antarmuka dalam satu tempat.
- Pendekatan ini membuat perubahan kecil berpotensi berdampak ke banyak area.

Dampak:

- Proses maintenance kode menjadi lebih lambat.
- Risiko bug saat menambah fitur baru atau melakukan refactor menjadi lebih tinggi.

Saran:

- Pecah route berdasarkan modul.
- Pisahkan CSS dan JavaScript besar dari Blade ke file khusus.
- Ubah komponen UI yang kompleks menjadi partial atau komponen yang lebih kecil agar lebih mudah dirawat.

### 6. Dokumentasi dan automated testing belum cukup matang

Kekurangan:

- Dokumentasi utama belum sepenuhnya menggambarkan aplikasi aktif.
- Kualitas dokumentasi teknis belum konsisten.
- Automated test belum cukup kuat sebagai pengaman perubahan.

Dampak:

- Onboarding developer baru menjadi lebih lambat.
- Perubahan fitur lebih berisiko karena validasi otomatis belum menjadi safety net yang kuat.

Saran:

- Perbarui `README` agar fokus pada gambaran sistem yang benar-benar berjalan.
- Rapikan dokumentasi teknis menjadi satu sumber acuan utama.
- Perbaiki test suite yang ada lalu tambahkan test untuk alur penting seperti role access, maintenance report, finance, dan blasting.

## 10. Prioritas Perbaikan

Jika ingin dikerjakan bertahap, urutan prioritas yang disarankan adalah:

### Prioritas 1

- Amankan halaman aset publik dan form maintenance publik.
- Rapikan validasi dan proteksi submit publik.

### Prioritas 2

- Sederhanakan navigasi sesuai role.
- Tingkatkan kenyamanan penggunaan pada tampilan tabel dan perangkat mobile.

### Prioritas 3

- Rapikan struktur kode, route, dan Blade.
- Benahi dokumentasi dan test suite agar pengembangan berikutnya lebih aman.

## 11. Perbaikan Lanjutan yang Layak Dipertimbangkan

Selain perbaikan utama di atas, ada beberapa area lanjutan yang juga layak dimasukkan ke backlog pengembangan.

### 1. Workflow maintenance perlu dibuat lebih operasional

Kekurangan:

- Alur maintenance saat ini sudah mendukung submit, review, approve, dan reject, tetapi belum terlihat kuat pada aspek assignment pekerjaan, target penyelesaian, dan pemantauan progres lapangan.

Dampak:

- Laporan maintenance berisiko berhenti di tahap pencatatan tanpa tindak lanjut yang terukur.
- Tim akan lebih sulit mengetahui laporan mana yang sedang dikerjakan, terlambat, atau belum memiliki penanggung jawab.

Saran:

- Tambahkan field seperti `assigned_to`, `due_date`, dan status `on_progress`.
- Sediakan penanda overdue pada laporan maintenance.
- Tambahkan notifikasi atau reminder internal untuk tindak lanjut laporan yang belum selesai.

### 2. Audit log perlu diperluas ke lebih banyak modul

Kekurangan:

- Audit log sudah bermanfaat untuk sebagian proses, tetapi belum terlihat merata pada semua aksi penting lintas modul.

Dampak:

- Riwayat perubahan data penting bisa sulit ditelusuri.
- Investigasi kesalahan operasional, perubahan data yang tidak sesuai, atau dispute internal akan lebih sulit dilakukan.

Saran:

- Tambahkan audit log untuk penghapusan aset, perubahan status maintenance, perubahan user dan role, pengiriman blast, serta perubahan provider atau device WhatsApp.
- Tampilkan riwayat aktivitas penting pada halaman admin atau halaman detail data tertentu.

### 3. Pengelolaan file upload perlu kebijakan retensi

Kekurangan:

- Sistem memiliki beberapa lokasi penyimpanan file runtime seperti lampiran diskusi, voice note, dan upload gateway.
- Belum terlihat mekanisme cleanup otomatis yang konsisten.

Dampak:

- Storage server dapat cepat penuh.
- Backup menjadi lebih berat dan biaya infrastruktur dapat meningkat.

Saran:

- Tentukan kebijakan retensi file untuk diskusi, lampiran blast, dan file gateway.
- Buat job terjadwal untuk membersihkan file sementara atau file lama yang tidak lagi dipakai.
- Tambahkan monitoring kapasitas penyimpanan agar tim bisa bertindak sebelum storage penuh.

### 4. Mekanisme hapus data penting sebaiknya lebih aman

Kekurangan:

- Beberapa data bisnis penting tampaknya masih lebih dekat ke pola hapus permanen daripada arsip atau soft delete.

Dampak:

- Risiko kehilangan data akibat human error lebih tinggi.
- Pemulihan data yang terhapus akan lebih sulit jika tidak ada mekanisme restore yang baik.

Saran:

- Terapkan soft delete atau archive untuk aset, laporan maintenance, recipient, dan data pengguna.
- Pisahkan antara aksi `hapus`, `nonaktifkan`, dan `arsipkan` agar kontrol data lebih aman.

### 5. Monitoring operasional sistem masih bisa diperkuat

Kekurangan:

- Aplikasi ini terdiri dari lebih dari satu komponen operasional, yaitu Laravel app, queue, dan WhatsApp gateway.
- Belum terlihat satu dashboard ringkas yang memudahkan pemantauan kesehatan sistem secara menyeluruh.

Dampak:

- Gangguan seperti failed job, device gateway putus, atau antrean blast yang macet bisa terlambat diketahui.
- Respons tim terhadap insiden menjadi lebih lambat.

Saran:

- Buat dashboard kesehatan sistem untuk queue, failed jobs, status device, error gateway, dan penggunaan storage.
- Tambahkan alert sederhana untuk kondisi kritis seperti gateway disconnect atau job gagal berulang.

### 6. CI dan automated testing perlu dinaikkan levelnya

Kekurangan:

- Automated testing sudah ada, tetapi belum cukup kuat untuk menjadi safety net utama.
- Proses validasi perubahan belum terlihat terotomasi melalui pipeline CI.

Dampak:

- Risiko bug masuk ke branch utama lebih tinggi.
- Refactor atau penambahan fitur baru menjadi lebih menegangkan karena validasi otomatis belum kuat.

Saran:

- Perbaiki test suite yang ada sampai stabil dijalankan.
- Tambahkan pipeline CI minimal untuk menjalankan test, validasi konfigurasi, dan pemeriksaan dasar build.
- Prioritaskan test untuk alur permission, maintenance report, finance, dan blasting.

### 7. Dokumentasi proyek perlu disatukan dan dibuat lebih konsisten

Kekurangan:

- Dokumen proyek sudah cukup banyak, tetapi belum seluruhnya tersusun sebagai satu sumber referensi utama yang rapi dan konsisten.

Dampak:

- Developer baru membutuhkan waktu lebih lama untuk memahami sistem.
- Risiko munculnya informasi yang tidak sinkron antar dokumen menjadi lebih tinggi.

Saran:

- Jadikan satu dokumen utama sebagai entry point onboarding proyek.
- Pisahkan dokumen menjadi kategori yang jelas: produk, arsitektur, operasional, dan pengembangan.
- Tetapkan format dokumentasi yang konsisten agar mudah dirawat bersama.

## 12. Prioritas Lanjutan

Jika backlog utama sudah mulai tertangani, urutan lanjutan yang disarankan adalah:

### Prioritas Lanjutan 1

- Rapikan workflow maintenance dengan assignment, target waktu, dan reminder.
- Perluas audit log ke aksi bisnis yang paling sensitif.

### Prioritas Lanjutan 2

- Terapkan cleanup file dan monitoring storage.
- Perkuat monitoring operasional untuk queue dan gateway.

### Prioritas Lanjutan 3

- Stabilkan test suite dan bangun pipeline CI.
- Satukan dokumentasi proyek agar onboarding dan maintenance tim lebih mudah.
