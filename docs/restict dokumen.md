# Restict Dokumen

Dokumen internal ini berisi hal-hal penting dari web Sistem Operasional Yayasan YPIK. Dokumen ini bersifat terbatas karena memuat akses, role, maintenance, blasting, dan catatan keamanan sistem.

Terakhir diperbarui: 23 Juli 2026

## 1. Identitas Web

- Nama aplikasi: Sistem Operasional Yayasan YPIK
- Domain production: `https://soy.ypikpamjaya.com`
- Login umum: `https://soy.ypikpamjaya.com/login`
- Login Sistem Management: `https://soy.ypikpamjaya.com/system-management/login`
- Local development saat ini: `http://127.0.0.1:8010`
- Framework utama: Laravel 10
- Gateway WhatsApp: Node.js gateway dengan queue support

## 2. Role Utama

Role yang tersedia di sistem:

- `User`
- `Admin`
- `IT Support`
- `Asset Manager`
- `Finance`
- `Pembina`
- `Blasting`
- `QC`
- `Sistem Management`

Role `Sistem Management` adalah role tertinggi untuk kontrol operasional sistem. Role ini dipisahkan dari login umum dan tidak ikut mati saat mode maintenance aktif.

## 3. Akun Sistem Management

Akun awal yang dibuat melalui seeder:

- Email: `sistem.management@ypik.local`
- Password awal: `System-Management-123!`
- Halaman login: `https://soy.ypikpamjaya.com/system-management/login`

Catatan penting:

- Password awal wajib diganti setelah production siap.
- Jangan kirim password melalui grup publik.
- Gunakan akun ini hanya untuk kebutuhan kontrol sistem, audit, maintenance, dan pemulihan akses.

## 4. Cara Memasukkan Akun dan Struktur DB

Untuk production baru atau server yang belum memiliki role Sistem Management, jalankan:

```bash
php artisan migrate
php artisan db:seed --class=UserSeeder
php artisan optimize:clear
```

Jika konfigurasi environment berubah, jalankan:

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

Seeder akan membuat atau memperbarui akun `sistem.management@ypik.local` dengan role `Sistem Management`.

## 5. Modul Sistem Management

Halaman Sistem Management dibuat dengan sidebar dan dropdown agar tiap fungsi terpisah rapi.

Menu penting:

- `Ringkasan`: melihat overview kesehatan aplikasi.
- `Status Sistem`: cek Laravel app, database, cache, queue, storage logs, WhatsApp gateway, email blast, dan maintenance mode.
- `Maintenance`: mematikan akses web untuk semua role termasuk superadmin/admin, tetapi Sistem Management tetap bisa login.
- `Alur Blast`: melihat alur blasting WhatsApp dan email untuk mencari titik error data.
- `Audit Akses`: melihat siapa yang masuk web, IP, browser, user agent, endpoint/API yang dipakai, dan lokasi berbasis IP.
- `Password`: reset password seluruh role, termasuk admin/superadmin jika ada.
- `Role`: mengubah restrict akses halaman per role.
- `AI`: membuat draft fitur dan menjalankan request AI jika executor sudah tersambung.
- `API Tester`: menembak API langsung dari web seperti versi ringan Postman.
- `CMS`: mengubah label, notice, content width, dan custom CSS tampilan web.
- `Fitur`: membuat draft fitur baru dan mengaktifkan/nonaktifkan feature flag.
- `Akses Fitur`: mengaktifkan/nonaktifkan modul utama secara global.
- `Arsip`: melihat arsip log blasting yang tetap disimpan untuk Sistem Management.

## 6. Maintenance Mode

Maintenance mode menyimpan status di setting `system.maintenance`.

Saat maintenance aktif:

- User biasa tidak bisa membuka aplikasi.
- Admin/superadmin tidak bisa membuka aplikasi.
- Role selain Sistem Management akan diarahkan ke halaman maintenance.
- Sistem Management tetap bisa login dan mengelola web.

Tujuan fitur ini adalah maintenance berkala tanpa kehilangan akses kontrol utama.

## 7. Akses Fitur Global

Halaman `Akses Fitur` mengontrol modul utama aplikasi.

Fitur yang bisa dikontrol:

- Dashboard
- Diskusi
- Asset Management
- User Management
- Blasting WhatsApp & Email
- Reminder
- Tema Website
- Finance
- Sistem Management

Catatan:

- Jika fitur dimatikan, menu akan hilang dari sidebar.
- Route fitur tersebut ikut diblok oleh middleware.
- Sistem Management dikunci tetap aktif agar akses pemulihan tidak ikut mati.

## 8. Modul Blasting WhatsApp dan Email

Modul blasting sudah diarahkan agar lebih aman untuk pengiriman besar.

Hal penting:

- Mode default email blast: `queue`
- Mode default WhatsApp blast: `queue`
- WhatsApp gateway timeout diperpanjang melalui `WHATSAPP_GATEWAY_TIMEOUT`.
- Status `FAILED` bisa langsung di-retry.
- Status WhatsApp yang masih `Antrian Gateway` juga bisa di-retry jika masih berada di state pending gateway.
- Tampilan blasting dibuat lebih sederhana dengan palette yang konsisten.
- Setting lanjutan yang tidak perlu dilihat setiap hari disembunyikan dari UI utama.

Environment penting:

```env
QUEUE_CONNECTION=database
BLAST_EMAIL_MODE=queue
BLAST_WHATSAPP_MODE=queue
WHATSAPP_GATEWAY_BASE_URL=http://127.0.0.1:3010
WHATSAPP_GATEWAY_TIMEOUT=60
```

Worker Laravel yang perlu aktif untuk queue blasting:

```bash
php artisan queue:work --queue=blast-whatsapp-high,blast-whatsapp-normal,blast-whatsapp-low,blast-email-high,blast-email-normal,blast-email-low,default --tries=3 --timeout=120
```

Gateway WhatsApp dijalankan dari folder `whatsapp-gateway`:

```bash
npm install
npm run start
```

Jika menggunakan worker gateway terpisah:

```bash
npm run worker
```

## 9. Audit Akses dan Lokasi

Audit akses otomatis mencatat aktivitas request web jika `SYSTEM_ACCESS_AUDIT_ENABLED=true`.

Data yang dicatat:

- User yang login.
- IP address.
- Browser dan user agent.
- Method dan path/API yang dipakai.
- Waktu akses.
- Lokasi berbasis IP jika endpoint geolocation diisi.

Catatan akurasi lokasi:

- Lokasi dicatat dari IP, bukan GPS perangkat.
- Akurasi bergantung pada provider IP/geolocation.
- Untuk hasil lebih akurat, isi `IP_GEOLOCATION_ENDPOINT` dengan provider geolocation yang stabil.

## 10. AI Developer di Web

Fitur AI di Sistem Management sudah disiapkan sebagai konektor.

Environment yang perlu diisi:

```env
AI_FEATURE_BUILDER_ENDPOINT=
AI_FEATURE_BUILDER_TOKEN=
AI_FEATURE_EXECUTOR_ENDPOINT=
AI_FEATURE_EXECUTOR_TOKEN=
```

Cara kerja:

- `AI_FEATURE_BUILDER_ENDPOINT` dipakai untuk membuat draft fitur dari input modul dan tujuan fitur.
- `AI_FEATURE_EXECUTOR_ENDPOINT` dipakai jika AI ingin menjalankan perubahan fitur langsung dari web.
- Jika endpoint executor kosong, web akan menampilkan pesan bahwa AI executor belum tersambung.
- Mode `plan` dipakai untuk meminta rencana perubahan.
- Mode `apply` dipakai untuk meminta AI menjalankan perubahan, dengan catatan executor eksternal harus tersedia dan aman.

Catatan keamanan:

- Jangan isi token AI di file yang ikut dipush ke repository.
- Simpan token hanya di `.env` production.
- Executor AI harus tetap mewajibkan git diff dan test sebelum perubahan diterapkan.

## 11. API Tester

API Tester berada di Sistem Management dan digunakan untuk menembak API langsung dari web.

Kegunaan:

- Mengecek endpoint internal/eksternal saat error.
- Mengirim request `GET`, `POST`, `PUT`, `PATCH`, `DELETE`, `HEAD`, dan `OPTIONS`.
- Mengirim header JSON.
- Mengirim body JSON, form, atau raw.
- Melihat response status, durasi, header, dan body.

Environment:

```env
SYSTEM_API_TESTER_ALLOW_PRIVATE_NETWORK=true
SYSTEM_API_TESTER_MAX_RESPONSE_BYTES=200000
```

Catatan:

- Gunakan hanya untuk debugging oleh pihak yang dipercaya.
- Hindari menyimpan token API rahasia di screenshot atau dokumen publik.

## 12. CMS Tampilan

CMS di Sistem Management mengatur tampilan ringan tanpa edit kode langsung.

Yang bisa diubah:

- Brand short.
- Label sidebar.
- Notice bar.
- Content width.
- Custom CSS.

Catatan:

- Custom CSS disanitasi sebelum disimpan.
- Jika tampilan rusak setelah custom CSS, kosongkan custom CSS dari halaman CMS.

## 13. Hal Penting Saat Deploy

Setelah pull commit terbaru di production, jalankan:

```bash
composer install --no-dev --optimize-autoloader
npm install
npm run build
php artisan migrate
php artisan db:seed --class=UserSeeder
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Pastikan service berikut berjalan:

- Web server PHP/Laravel.
- Database.
- Queue worker Laravel.
- WhatsApp gateway Node.js.
- Worker WhatsApp gateway jika memakai queue Redis/BullMQ.

## 14. Prioritas Keamanan

Hal yang wajib dijaga:

- Ganti password awal semua akun setelah deploy.
- Batasi akses System Management hanya untuk orang yang benar-benar berwenang.
- Jangan push file `.env`.
- Jangan simpan token AI, email, WhatsApp gateway, atau API key di dokumen publik.
- Aktifkan HTTPS untuk domain production.
- Pastikan backup database berjalan berkala.
- Cek audit akses jika ada aktivitas login mencurigakan.
- Jangan matikan fitur `Sistem Management` dari konfigurasi karena itu jalur pemulihan utama.

## 15. Checklist Cepat Jika Ada Error

Jika blasting WhatsApp lama di `Antrian Gateway`:

- Cek status gateway di Sistem Management > Status Sistem.
- Cek `WHATSAPP_GATEWAY_BASE_URL`.
- Cek `WHATSAPP_GATEWAY_TIMEOUT`.
- Cek queue worker Laravel.
- Cek worker gateway Node.js.
- Gunakan tombol retry di log blasting.

Jika web 504:

- Cek Nginx/PHP timeout.
- Pastikan proses blast berjalan lewat queue, bukan request langsung.
- Cek worker queue.
- Cek log Laravel di `storage/logs`.

Jika user tidak bisa masuk halaman:

- Cek System Management > Role.
- Cek System Management > Akses Fitur.
- Cek apakah maintenance mode aktif.

Jika System Management tidak bisa login:

- Pastikan route `/system-management/login` tersedia.
- Jalankan migration dan seeder.
- Reset password akun `sistem.management@ypik.local` melalui database atau seeder.
