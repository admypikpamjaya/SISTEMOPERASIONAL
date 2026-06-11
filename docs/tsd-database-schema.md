# TSD - Database Schema

Dokumen ini disusun untuk kebutuhan Technical Specification Document (TSD) Sistem Operasional Yayasan YPIK. Format mengikuti contoh TSD yang memuat list schema, list table, dan data dictionary per tabel.

## Sumber Schema

| Item | Nilai | Keterangan |
| --- | --- | --- |
| DB Connection | mysql | Berdasarkan `.env` dan `.env.example`. |
| DB Name Project | soy_ypik | Berdasarkan `DB_DATABASE` pada project. |
| DB Name Dump | u1606429_soy_ypik | Berdasarkan file SQL dump `u1606429_soy_ypik (1).sql`. |
| SQL Dump | 04 Mei 2026 | Dump phpMyAdmin MariaDB 10.11.16. |
| Migration Tambahan | 23-24 Mei 2026 | Migration setelah dump ikut dimasukkan: asset import, recipient notifikasi maintenance, kolom import aset, purchase price, dan periode depresiasi. |
| Tanggal Penyusunan | 25 Mei 2026 | Disusun dari project lokal. |

## Catatan Validasi

- Daftar tabel diambil dari SQL dump dan seluruh migration Laravel, lalu divalidasi dengan model aplikasi.
- Model legacy `App\Models\Recipient` mengarah ke tabel `recipients`, tetapi tabel tersebut tidak ditemukan pada migration maupun SQL dump, sehingga tidak dimasukkan sebagai tabel database aktif.

## List Schema

| No | Schema / Database | DBMS | Keterangan |
| ---: | --- | --- | --- |
| 1 | soy_ypik | MySQL/MariaDB | Nama database pada environment project Laravel. |
| 2 | u1606429_soy_ypik | MariaDB | Nama database pada SQL dump yang dipakai sebagai validasi struktur existing. |

## List Table

| No | Table | Modul | Jumlah Kolom | Keterangan |
| ---: | --- | --- | ---: | --- |
| 1 | air_conditioner_details | Asset & Maintenance | 6 | Detail spesifik aset kategori AC. |
| 2 | announcement_logs | Announcement & Notification | 12 | Log pengiriman dan tracking pengumuman. |
| 3 | announcements | Announcement & Notification | 7 | Pengumuman internal/aplikasi. |
| 4 | asset_import_batches | Asset & Maintenance | 12 | Riwayat batch import aset dari file. |
| 5 | assets | Asset & Maintenance | 13 | Master aset yayasan. |
| 6 | audit_logs | Audit | 8 | Log audit aktivitas user. |
| 7 | billings | Billing & Tunggakan | 7 | Tagihan parent user. |
| 8 | blast_employee_ypik_recipients | Blast & Recipient | 13 | Master penerima blast karyawan YPIK. |
| 9 | blast_logs | Blast & Recipient | 12 | Log pengiriman pesan blast. |
| 10 | blast_message_templates | Blast & Recipient | 8 | Template pesan blast. |
| 11 | blast_messages | Blast & Recipient | 15 | Master kampanye pesan blast. |
| 12 | blast_recipients | Blast & Recipient | 12 | Master penerima blast siswa/wali. |
| 13 | blast_targets | Blast & Recipient | 5 | Target penerima per pesan blast. |
| 14 | blast_templates | Blast & Recipient | 9 | Template kampanye/pesan blast legacy. |
| 15 | computer_components | Asset & Maintenance | 7 | Detail komponen untuk aset komputer. |
| 16 | discussion_channels | Discussion | 6 | Channel diskusi. |
| 17 | discussion_messages | Discussion | 16 | Pesan diskusi dan lampiran. |
| 18 | failed_jobs | Framework | 7 | Queue failed jobs Laravel. |
| 19 | finance_account_logs | Finance | 8 | Log perubahan akun finance. |
| 20 | finance_accounts | Finance | 11 | Chart of account finance. |
| 21 | finance_asset_policies | Finance | 12 | Kebijakan penyusutan aset finance. |
| 22 | finance_depreciation_calculation_logs | Finance | 13 | Log kalkulasi penyusutan. |
| 23 | finance_depreciation_histories | Finance | 15 | Detail hasil penyusutan aset. |
| 24 | finance_depreciation_runs | Finance | 11 | Header proses penyusutan per periode. |
| 25 | finance_general_ledger_batches | Finance | 13 | Batch import general ledger. |
| 26 | finance_general_ledger_entries | Finance | 25 | Detail entri general ledger. |
| 27 | finance_invoice_items | Finance | 13 | Detail item invoice/jurnal finance. |
| 28 | finance_invoice_notes | Finance | 6 | Catatan invoice finance. |
| 29 | finance_invoices | Finance | 16 | Header invoice/jurnal finance. |
| 30 | finance_journal_entries | Finance | 11 | Jurnal finance ringkas. |
| 31 | finance_periods | Finance | 14 | Master periode finance. |
| 32 | finance_reconciliation_snapshots | Finance | 12 | Snapshot rekonsiliasi periode. |
| 33 | finance_report_review_logs | Finance | 7 | Log review laporan finance. |
| 34 | finance_report_snapshot_items | Finance | 9 | Detail item snapshot laporan finance. |
| 35 | finance_report_snapshots | Finance | 11 | Snapshot laporan finance. |
| 36 | finance_statement_batches | Finance | 14 | Batch import financial statement. |
| 37 | finance_statement_rows | Finance | 17 | Detail baris financial statement. |
| 38 | jobs | Framework | 7 | Queue jobs Laravel. |
| 39 | login_histories | Auth & User | 9 | Riwayat login pengguna. |
| 40 | maintenance_documentations | Asset & Maintenance | 5 | Dokumentasi file untuk maintenance log. |
| 41 | maintenance_logs | Asset & Maintenance | 11 | Log laporan/perawatan aset. |
| 42 | maintenance_notification_recipients | Asset & Maintenance | 6 | Daftar penerima email notifikasi maintenance. |
| 43 | message_attachments | Announcement & Notification | 9 | Lampiran pesan/pengumuman. |
| 44 | migrations | Framework | 3 | Riwayat migration Laravel. |
| 45 | parent_users | Parent | 7 | Master wali/orang tua. |
| 46 | password_reset_tokens | Auth & User | 3 | Token reset password. |
| 47 | payments | Billing & Tunggakan | 9 | Pembayaran tagihan. |
| 48 | personal_access_tokens | Auth & User | 10 | Token API Sanctum. |
| 49 | recipent_data_koperasi_tirta_jatik_utama | Blast & Recipient | 12 | Master penerima koperasi Tirta Jatik Utama. |
| 50 | reminders | Announcement & Notification | 13 | Pengingat terkait pengumuman/kegiatan. |
| 51 | tunggakan_blast_logs | Billing & Tunggakan | 16 | Ringkasan eksekusi blast tunggakan. |
| 52 | tunggakan_import_batches | Billing & Tunggakan | 10 | Batch import data tunggakan. |
| 53 | tunggakan_records | Billing & Tunggakan | 20 | Detail record tunggakan hasil import. |
| 54 | users | Auth & User | 8 | Data akun pengguna dan role aplikasi. |

## Data Dictionary

Keterangan kolom: `Nullable` berisi `YES` jika kolom boleh null dan `NO` jika wajib diisi. `Key` berisi `PK` untuk primary key, `UK` untuk unique key, `IDX` untuk index biasa, dan `FK -> table.column` untuk foreign key.

### Tabel air_conditioner_details

| Table Name | air_conditioner_details |
| --- | --- |
| Modul | Asset & Maintenance |
| Keterangan | Detail spesifik aset kategori AC. |
| Jumlah Kolom | 6 |
| Catatan | Updated by 2026_05_23_193000: dimension and power_rating changed to varchar(100). |

| No | Column Name | Data Type | Nullable | Default | Key | Extra |
| ---: | --- | --- | --- | --- | --- | --- |
| 1 | asset_id | char(36) | NO |  | PK; FK -> assets.id |  |
| 2 | brand | varchar(255) | NO |  |  |  |
| 3 | dimension | varchar(100) | NO |  |  |  |
| 4 | power_rating | varchar(100) | NO |  |  |  |
| 5 | created_at | timestamp | YES | NULL |  |  |
| 6 | updated_at | timestamp | YES | NULL |  |  |

### Tabel announcement_logs

| Table Name | announcement_logs |
| --- | --- |
| Modul | Announcement & Notification |
| Keterangan | Log pengiriman dan tracking pengumuman. |
| Jumlah Kolom | 12 |

| No | Column Name | Data Type | Nullable | Default | Key | Extra |
| ---: | --- | --- | --- | --- | --- | --- |
| 1 | id | bigint(20) UNSIGNED | NO |  | PK | auto_increment |
| 2 | announcement_id | bigint(20) UNSIGNED | NO |  | IDX; FK -> announcements.id |  |
| 3 | channel | varchar(255) | NO |  |  |  |
| 4 | target | varchar(255) | NO |  |  |  |
| 5 | status | varchar(255) | NO |  |  |  |
| 6 | response | text | YES | NULL |  |  |
| 7 | track_token | varchar(255) | YES | NULL | UK |  |
| 8 | opened_at | timestamp | YES | NULL |  |  |
| 9 | open_count | int(10) UNSIGNED | NO | 0 |  |  |
| 10 | sent_at | timestamp | YES | NULL |  |  |
| 11 | created_at | timestamp | YES | NULL |  |  |
| 12 | updated_at | timestamp | YES | NULL |  |  |

### Tabel announcements

| Table Name | announcements |
| --- | --- |
| Modul | Announcement & Notification |
| Keterangan | Pengumuman internal/aplikasi. |
| Jumlah Kolom | 7 |

| No | Column Name | Data Type | Nullable | Default | Key | Extra |
| ---: | --- | --- | --- | --- | --- | --- |
| 1 | id | bigint(20) UNSIGNED | NO |  | PK | auto_increment |
| 2 | title | varchar(255) | NO |  |  |  |
| 3 | message | text | NO |  |  |  |
| 4 | attachment_path | varchar(255) | YES | NULL |  |  |
| 5 | created_by | char(36) | NO |  | IDX; FK -> users.id |  |
| 6 | created_at | timestamp | YES | NULL |  |  |
| 7 | updated_at | timestamp | YES | NULL |  |  |

### Tabel asset_import_batches

| Table Name | asset_import_batches |
| --- | --- |
| Modul | Asset & Maintenance |
| Keterangan | Riwayat batch import aset dari file. |
| Jumlah Kolom | 12 |
| Catatan | Created by 2026_05_23_213000_create_asset_import_batches_table. |

| No | Column Name | Data Type | Nullable | Default | Key | Extra |
| ---: | --- | --- | --- | --- | --- | --- |
| 1 | id | char(36) | NO |  | PK |  |
| 2 | category | enum('AC','COMPUTER','OTHER') | NO |  | IDX:asset_import_batches_category_source_type_index |  |
| 3 | source_type | enum('excel','csv') | NO |  | IDX:asset_import_batches_category_source_type_index |  |
| 4 | source_file_name | varchar(255) | NO |  |  |  |
| 5 | processed_rows | int(10) UNSIGNED | NO | 0 |  |  |
| 6 | imported_rows | int(10) UNSIGNED | NO | 0 |  |  |
| 7 | sheet_count | int(10) UNSIGNED | NO | 0 |  |  |
| 8 | sheet_names | json | YES | NULL |  |  |
| 9 | metadata | json | YES | NULL |  |  |
| 10 | imported_by | char(36) | YES | NULL | IDX; FK -> users.id |  |
| 11 | created_at | timestamp | YES | NULL | IDX |  |
| 12 | updated_at | timestamp | YES | NULL |  |  |

### Tabel assets

| Table Name | assets |
| --- | --- |
| Modul | Asset & Maintenance |
| Keterangan | Master aset yayasan. |
| Jumlah Kolom | 13 |
| Catatan | Updated by 2026_05_24_090000 and 2026_05_24_103000: import tracking and purchase price columns added. |

| No | Column Name | Data Type | Nullable | Default | Key | Extra |
| ---: | --- | --- | --- | --- | --- | --- |
| 1 | id | char(36) | NO |  | PK |  |
| 2 | category | enum('AC','OTHER','COMPUTER') | NO |  |  |  |
| 3 | account_code | varchar(255) | NO |  | UK |  |
| 4 | serial_number | varchar(255) | YES | NULL | UK |  |
| 5 | location | varchar(255) | NO |  |  |  |
| 6 | purchase_year | varchar(255) | YES | NULL |  |  |
| 7 | purchase_price | decimal(18,2) | YES | NULL | IDX |  |
| 8 | qr_code_path | varchar(255) | YES | NULL |  |  |
| 9 | last_import_file_name | varchar(255) | YES | NULL | IDX |  |
| 10 | last_imported_at | timestamp | YES | NULL | IDX |  |
| 11 | created_at | timestamp | YES | NULL |  |  |
| 12 | updated_at | timestamp | YES | NULL |  |  |
| 13 | unit | enum('TK','SD','Yayasan') | YES | NULL |  |  |

### Tabel audit_logs

| Table Name | audit_logs |
| --- | --- |
| Modul | Audit |
| Keterangan | Log audit aktivitas user. |
| Jumlah Kolom | 8 |

| No | Column Name | Data Type | Nullable | Default | Key | Extra |
| ---: | --- | --- | --- | --- | --- | --- |
| 1 | id | bigint(20) UNSIGNED | NO |  | PK | auto_increment |
| 2 | user_id | char(36) | NO |  | IDX; FK -> users.id |  |
| 3 | action | varchar(255) | NO |  |  |  |
| 4 | entity | varchar(255) | NO |  |  |  |
| 5 | entity_id | bigint(20) UNSIGNED | NO |  |  |  |
| 6 | payload | longtext | YES | NULL |  |  |
| 7 | created_at | timestamp | YES | NULL |  |  |
| 8 | updated_at | timestamp | YES | NULL |  |  |

### Tabel billings

| Table Name | billings |
| --- | --- |
| Modul | Billing & Tunggakan |
| Keterangan | Tagihan parent user. |
| Jumlah Kolom | 7 |

| No | Column Name | Data Type | Nullable | Default | Key | Extra |
| ---: | --- | --- | --- | --- | --- | --- |
| 1 | id | bigint(20) UNSIGNED | NO |  | PK | auto_increment |
| 2 | parent_id | bigint(20) UNSIGNED | NO |  | IDX; FK -> parent_users.id |  |
| 3 | amount | decimal(12,2) | NO |  |  |  |
| 4 | due_date | date | NO |  |  |  |
| 5 | status | enum('PENDING','PAID','EXPIRED') | NO | 'PENDING' |  |  |
| 6 | created_at | timestamp | YES | NULL |  |  |
| 7 | updated_at | timestamp | YES | NULL |  |  |

### Tabel blast_employee_ypik_recipients

| Table Name | blast_employee_ypik_recipients |
| --- | --- |
| Modul | Blast & Recipient |
| Keterangan | Master penerima blast karyawan YPIK. |
| Jumlah Kolom | 13 |

| No | Column Name | Data Type | Nullable | Default | Key | Extra |
| ---: | --- | --- | --- | --- | --- | --- |
| 1 | id | char(36) | NO |  | PK |  |
| 2 | nama_karyawan | varchar(255) | NO |  |  |  |
| 3 | instansi | varchar(255) | YES | NULL | IDX |  |
| 4 | nama_wali | varchar(255) | YES | NULL |  |  |
| 5 | wa_karyawan | varchar(255) | YES | NULL | IDX |  |
| 6 | email_karyawan | varchar(255) | YES | NULL | IDX |  |
| 7 | catatan | text | YES | NULL |  |  |
| 8 | source | varchar(255) | YES | NULL |  |  |
| 9 | dataset | varchar(40) | NO | 'pam_jaya' | IDX |  |
| 10 | is_valid | tinyint(1) | NO | 0 | IDX |  |
| 11 | validation_error | text | YES | NULL |  |  |
| 12 | created_at | timestamp | YES | NULL |  |  |
| 13 | updated_at | timestamp | YES | NULL |  |  |

### Tabel blast_logs

| Table Name | blast_logs |
| --- | --- |
| Modul | Blast & Recipient |
| Keterangan | Log pengiriman pesan blast. |
| Jumlah Kolom | 12 |

| No | Column Name | Data Type | Nullable | Default | Key | Extra |
| ---: | --- | --- | --- | --- | --- | --- |
| 1 | id | bigint(20) UNSIGNED | NO |  | PK | auto_increment |
| 2 | blast_message_id | char(36) | NO |  | IDX; FK -> blast_messages.id |  |
| 3 | blast_target_id | bigint(20) UNSIGNED | NO |  | IDX; FK -> blast_targets.id |  |
| 4 | device_id | varchar(64) | YES | NULL |  |  |
| 5 | status | enum('PENDING','SENT','FAILED') | NO |  |  |  |
| 6 | response | text | YES | NULL |  |  |
| 7 | attempt | tinyint(3) UNSIGNED | NO | 0 |  |  |
| 8 | sent_at | timestamp | YES | NULL |  |  |
| 9 | created_at | timestamp | YES | NULL |  |  |
| 10 | updated_at | timestamp | YES | NULL |  |  |
| 11 | message_snapshot | longtext | YES | NULL |  |  |
| 12 | error_message | text | YES | NULL |  |  |

### Tabel blast_message_templates

| Table Name | blast_message_templates |
| --- | --- |
| Modul | Blast & Recipient |
| Keterangan | Template pesan blast. |
| Jumlah Kolom | 8 |

| No | Column Name | Data Type | Nullable | Default | Key | Extra |
| ---: | --- | --- | --- | --- | --- | --- |
| 1 | id | char(36) | NO |  | PK |  |
| 2 | name | varchar(255) | NO |  |  |  |
| 3 | channel | enum('email','whatsapp') | NO |  |  |  |
| 4 | content | text | NO |  |  |  |
| 5 | is_active | tinyint(1) | NO | 1 |  |  |
| 6 | created_by | char(36) | YES | NULL |  |  |
| 7 | created_at | timestamp | YES | NULL |  |  |
| 8 | updated_at | timestamp | YES | NULL |  |  |

### Tabel blast_messages

| Table Name | blast_messages |
| --- | --- |
| Modul | Blast & Recipient |
| Keterangan | Master kampanye pesan blast. |
| Jumlah Kolom | 15 |

| No | Column Name | Data Type | Nullable | Default | Key | Extra |
| ---: | --- | --- | --- | --- | --- | --- |
| 1 | id | char(36) | NO |  | PK |  |
| 2 | channel | enum('EMAIL','WHATSAPP') | NO |  |  |  |
| 3 | subject | varchar(255) | YES | NULL |  |  |
| 4 | message | text | NO |  |  |  |
| 5 | meta | longtext | YES | NULL |  |  |
| 6 | campaign_status | varchar(255) | NO | 'QUEUED' | IDX |  |
| 7 | priority | varchar(255) | NO | 'normal' |  |  |
| 8 | scheduled_at | timestamp | YES | NULL | IDX |  |
| 9 | started_at | timestamp | YES | NULL |  |  |
| 10 | paused_at | timestamp | YES | NULL |  |  |
| 11 | completed_at | timestamp | YES | NULL |  |  |
| 12 | attachment_path | varchar(255) | YES | NULL |  |  |
| 13 | created_by | char(36) | NO |  | IDX; FK -> users.id |  |
| 14 | created_at | timestamp | YES | NULL |  |  |
| 15 | updated_at | timestamp | YES | NULL |  |  |

### Tabel blast_recipients

| Table Name | blast_recipients |
| --- | --- |
| Modul | Blast & Recipient |
| Keterangan | Master penerima blast siswa/wali. |
| Jumlah Kolom | 12 |

| No | Column Name | Data Type | Nullable | Default | Key | Extra |
| ---: | --- | --- | --- | --- | --- | --- |
| 1 | id | char(36) | NO |  | PK |  |
| 2 | nama_siswa | varchar(255) | NO |  |  |  |
| 3 | kelas | varchar(255) | NO |  |  |  |
| 4 | nama_wali | varchar(255) | NO |  |  |  |
| 5 | wa_wali | varchar(255) | YES | NULL | IDX |  |
| 6 | wa_wali_2 | varchar(255) | YES | NULL | IDX |  |
| 7 | email_wali | varchar(255) | YES | NULL | IDX |  |
| 8 | catatan | text | YES | NULL |  |  |
| 9 | is_valid | tinyint(1) | NO | 0 | IDX |  |
| 10 | validation_error | text | YES | NULL |  |  |
| 11 | created_at | timestamp | YES | NULL |  |  |
| 12 | updated_at | timestamp | YES | NULL |  |  |

### Tabel blast_targets

| Table Name | blast_targets |
| --- | --- |
| Modul | Blast & Recipient |
| Keterangan | Target penerima per pesan blast. |
| Jumlah Kolom | 5 |

| No | Column Name | Data Type | Nullable | Default | Key | Extra |
| ---: | --- | --- | --- | --- | --- | --- |
| 1 | id | bigint(20) UNSIGNED | NO |  | PK | auto_increment |
| 2 | blast_message_id | char(36) | NO |  | IDX; FK -> blast_messages.id |  |
| 3 | target | varchar(255) | NO |  |  |  |
| 4 | created_at | timestamp | YES | NULL |  |  |
| 5 | updated_at | timestamp | YES | NULL |  |  |

### Tabel blast_templates

| Table Name | blast_templates |
| --- | --- |
| Modul | Blast & Recipient |
| Keterangan | Template kampanye/pesan blast legacy. |
| Jumlah Kolom | 9 |

| No | Column Name | Data Type | Nullable | Default | Key | Extra |
| ---: | --- | --- | --- | --- | --- | --- |
| 1 | id | char(36) | NO |  | PK |  |
| 2 | name | varchar(255) | NO |  |  |  |
| 3 | channel | enum('EMAIL','WHATSAPP') | NO |  |  |  |
| 4 | subject | varchar(255) | YES | NULL |  |  |
| 5 | body | text | NO |  |  |  |
| 6 | is_active | tinyint(1) | NO | 1 |  |  |
| 7 | created_by | char(36) | NO |  |  |  |
| 8 | created_at | timestamp | YES | NULL |  |  |
| 9 | updated_at | timestamp | YES | NULL |  |  |

### Tabel computer_components

| Table Name | computer_components |
| --- | --- |
| Modul | Asset & Maintenance |
| Keterangan | Detail komponen untuk aset komputer. |
| Jumlah Kolom | 7 |

| No | Column Name | Data Type | Nullable | Default | Key | Extra |
| ---: | --- | --- | --- | --- | --- | --- |
| 1 | asset_id | char(36) | NO |  | IDX; FK -> assets.id |  |
| 2 | component_type | enum('Monitor','Motherboard','Processor','RAM','Storage','GPU','Keyboard / Mouse') | NO |  |  |  |
| 3 | brand | varchar(255) | YES | NULL |  |  |
| 4 | specification | varchar(255) | YES | NULL |  |  |
| 5 | serial_number | varchar(255) | YES | NULL |  |  |
| 6 | created_at | timestamp | YES | NULL |  |  |
| 7 | updated_at | timestamp | YES | NULL |  |  |

### Tabel discussion_channels

| Table Name | discussion_channels |
| --- | --- |
| Modul | Discussion |
| Keterangan | Channel diskusi. |
| Jumlah Kolom | 6 |

| No | Column Name | Data Type | Nullable | Default | Key | Extra |
| ---: | --- | --- | --- | --- | --- | --- |
| 1 | id | bigint(20) UNSIGNED | NO |  | PK | auto_increment |
| 2 | name | varchar(255) | NO |  | UK |  |
| 3 | description | varchar(255) | YES | NULL |  |  |
| 4 | is_active | tinyint(1) | NO | 1 |  |  |
| 5 | created_at | timestamp | YES | NULL |  |  |
| 6 | updated_at | timestamp | YES | NULL |  |  |

### Tabel discussion_messages

| Table Name | discussion_messages |
| --- | --- |
| Modul | Discussion |
| Keterangan | Pesan diskusi dan lampiran. |
| Jumlah Kolom | 16 |

| No | Column Name | Data Type | Nullable | Default | Key | Extra |
| ---: | --- | --- | --- | --- | --- | --- |
| 1 | id | bigint(20) UNSIGNED | NO |  | PK; IDX:discussion_messages_channel_id_id_index | auto_increment |
| 2 | channel_id | bigint(20) UNSIGNED | NO |  | IDX:discussion_messages_channel_id_id_index; IDX:discussion_messages_channel_id_created_at_index; IDX:discussion_msg_channel_reply_index; FK -> discussion_channels.id |  |
| 3 | user_id | char(36) | YES | NULL | IDX; FK -> users.id |  |
| 4 | message | text | YES | NULL |  |  |
| 5 | reply_to_message_id | bigint(20) UNSIGNED | YES | NULL | IDX; IDX:discussion_msg_channel_reply_index; FK -> discussion_messages.id |  |
| 6 | attachment_path | varchar(255) | YES | NULL |  |  |
| 7 | attachment_name | varchar(255) | YES | NULL |  |  |
| 8 | attachment_size | bigint(20) UNSIGNED | YES | NULL |  |  |
| 9 | voice_note_path | varchar(255) | YES | NULL |  |  |
| 10 | voice_note_name | varchar(255) | YES | NULL |  |  |
| 11 | voice_note_size | bigint(20) UNSIGNED | YES | NULL |  |  |
| 12 | pinned_at | timestamp | YES | NULL | IDX |  |
| 13 | pin_expires_at | timestamp | YES | NULL | IDX |  |
| 14 | pinned_by | char(36) | YES | NULL | IDX; FK -> users.id |  |
| 15 | created_at | timestamp | YES | NULL | IDX:discussion_messages_channel_id_created_at_index |  |
| 16 | updated_at | timestamp | YES | NULL |  |  |

### Tabel failed_jobs

| Table Name | failed_jobs |
| --- | --- |
| Modul | Framework |
| Keterangan | Queue failed jobs Laravel. |
| Jumlah Kolom | 7 |

| No | Column Name | Data Type | Nullable | Default | Key | Extra |
| ---: | --- | --- | --- | --- | --- | --- |
| 1 | id | bigint(20) UNSIGNED | NO |  | PK | auto_increment |
| 2 | uuid | varchar(255) | NO |  | UK |  |
| 3 | connection | text | NO |  |  |  |
| 4 | queue | text | NO |  |  |  |
| 5 | payload | longtext | NO |  |  |  |
| 6 | exception | longtext | NO |  |  |  |
| 7 | failed_at | timestamp | NO | current_timestamp() |  |  |

### Tabel finance_account_logs

| Table Name | finance_account_logs |
| --- | --- |
| Modul | Finance |
| Keterangan | Log perubahan akun finance. |
| Jumlah Kolom | 8 |

| No | Column Name | Data Type | Nullable | Default | Key | Extra |
| ---: | --- | --- | --- | --- | --- | --- |
| 1 | id | bigint(20) UNSIGNED | NO |  | PK | auto_increment |
| 2 | finance_account_id | char(36) | YES | NULL | IDX; FK -> finance_accounts.id |  |
| 3 | action | varchar(32) | NO |  |  |  |
| 4 | before_data | longtext | YES | NULL |  |  |
| 5 | after_data | longtext | YES | NULL |  |  |
| 6 | actor_id | char(36) | YES | NULL | IDX; FK -> users.id |  |
| 7 | created_at | timestamp | YES | NULL | IDX |  |
| 8 | updated_at | timestamp | YES | NULL |  |  |

### Tabel finance_accounts

| Table Name | finance_accounts |
| --- | --- |
| Modul | Finance |
| Keterangan | Chart of account finance. |
| Jumlah Kolom | 11 |

| No | Column Name | Data Type | Nullable | Default | Key | Extra |
| ---: | --- | --- | --- | --- | --- | --- |
| 1 | id | char(36) | NO |  | PK |  |
| 2 | code | varchar(64) | NO |  | UK; IDX:finance_accounts_class_no_code_index |  |
| 3 | name | varchar(255) | NO |  |  |  |
| 4 | type | varchar(64) | NO |  | IDX |  |
| 5 | class_no | tinyint(3) UNSIGNED | NO |  | IDX:finance_accounts_class_no_code_index |  |
| 6 | is_active | tinyint(1) | NO | 1 | IDX |  |
| 7 | created_by | char(36) | YES | NULL | IDX; FK -> users.id |  |
| 8 | updated_by | char(36) | YES | NULL | IDX; FK -> users.id |  |
| 9 | meta | longtext | YES | NULL |  |  |
| 10 | created_at | timestamp | YES | NULL |  |  |
| 11 | updated_at | timestamp | YES | NULL |  |  |

### Tabel finance_asset_policies

| Table Name | finance_asset_policies |
| --- | --- |
| Modul | Finance |
| Keterangan | Kebijakan penyusutan aset finance. |
| Jumlah Kolom | 12 |

| No | Column Name | Data Type | Nullable | Default | Key | Extra |
| ---: | --- | --- | --- | --- | --- | --- |
| 1 | id | char(36) | NO |  | PK |  |
| 2 | asset_id | char(36) | NO |  | UK:finance_asset_policies_unique_revision; IDX; FK -> assets.id |  |
| 3 | revision_no | int(10) UNSIGNED | NO |  | UK:finance_asset_policies_unique_revision |  |
| 4 | method | enum('STRAIGHT_LINE') | NO | 'STRAIGHT_LINE' |  |  |
| 5 | acquisition_cost | decimal(18,2) | NO |  |  |  |
| 6 | residual_value | decimal(18,2) | NO | 0.00 |  |  |
| 7 | useful_life_months | smallint(5) UNSIGNED | NO |  |  |  |
| 8 | depreciation_start_date | date | NO |  |  |  |
| 9 | effective_period_id | char(36) | YES | NULL | IDX; FK -> finance_periods.id |  |
| 10 | notes | text | YES | NULL |  |  |
| 11 | created_by | char(36) | YES | NULL | IDX; FK -> users.id |  |
| 12 | created_at | timestamp | NO | current_timestamp() |  |  |

### Tabel finance_depreciation_calculation_logs

| Table Name | finance_depreciation_calculation_logs |
| --- | --- |
| Modul | Finance |
| Keterangan | Log kalkulasi penyusutan. |
| Jumlah Kolom | 13 |
| Catatan | Updated by 2026_05_23_100000: period_start_date and period_end_date added. |

| No | Column Name | Data Type | Nullable | Default | Key | Extra |
| ---: | --- | --- | --- | --- | --- | --- |
| 1 | id | bigint(20) UNSIGNED | NO |  | PK | auto_increment |
| 2 | asset_id | char(36) | NO |  | IDX:finance_depr_calc_logs_asset_period_index; FK -> assets.id |  |
| 3 | period_start_date | date | YES | NULL |  |  |
| 4 | period_end_date | date | YES | NULL |  |  |
| 5 | period_month | tinyint(3) UNSIGNED | NO |  | IDX:finance_depr_calc_logs_asset_period_index |  |
| 6 | period_year | smallint(5) UNSIGNED | NO |  | IDX:finance_depr_calc_logs_asset_period_index |  |
| 7 | acquisition_cost | decimal(18,2) | NO |  |  |  |
| 8 | useful_life_months | int(10) UNSIGNED | NO |  |  |  |
| 9 | depreciation_per_month | decimal(18,2) | NO |  |  |  |
| 10 | calculated_by | char(36) | YES | NULL | IDX; FK -> users.id |  |
| 11 | calculated_at | timestamp | NO | current_timestamp() | IDX |  |
| 12 | created_at | timestamp | YES | NULL |  |  |
| 13 | updated_at | timestamp | YES | NULL |  |  |

### Tabel finance_depreciation_histories

| Table Name | finance_depreciation_histories |
| --- | --- |
| Modul | Finance |
| Keterangan | Detail hasil penyusutan aset. |
| Jumlah Kolom | 15 |

| No | Column Name | Data Type | Nullable | Default | Key | Extra |
| ---: | --- | --- | --- | --- | --- | --- |
| 1 | id | char(36) | NO |  | PK |  |
| 2 | depreciation_run_id | char(36) | NO |  | UK:finance_depr_histories_unique_asset_per_run; FK -> finance_depreciation_runs.id |  |
| 3 | period_id | char(36) | NO |  | IDX:finance_depreciation_histories_period_id_asset_id_index; FK -> finance_periods.id |  |
| 4 | asset_id | char(36) | NO |  | UK:finance_depr_histories_unique_asset_per_run; IDX; IDX:finance_depreciation_histories_period_id_asset_id_index; FK -> assets.id |  |
| 5 | policy_id | char(36) | NO |  | IDX; FK -> finance_asset_policies.id |  |
| 6 | method | enum('STRAIGHT_LINE') | NO | 'STRAIGHT_LINE' |  |  |
| 7 | acquisition_cost_snapshot | decimal(18,2) | NO |  |  |  |
| 8 | residual_value_snapshot | decimal(18,2) | NO | 0.00 |  |  |
| 9 | useful_life_months_snapshot | smallint(5) UNSIGNED | NO |  |  |  |
| 10 | sequence_month | int(10) UNSIGNED | NO |  |  |  |
| 11 | accumulated_before | decimal(18,2) | NO | 0.00 |  |  |
| 12 | depreciation_amount | decimal(18,2) | NO |  |  |  |
| 13 | accumulated_after | decimal(18,2) | NO |  |  |  |
| 14 | book_value_end | decimal(18,2) | NO |  |  |  |
| 15 | created_at | timestamp | NO | current_timestamp() |  |  |

### Tabel finance_depreciation_runs

| Table Name | finance_depreciation_runs |
| --- | --- |
| Modul | Finance |
| Keterangan | Header proses penyusutan per periode. |
| Jumlah Kolom | 11 |

| No | Column Name | Data Type | Nullable | Default | Key | Extra |
| ---: | --- | --- | --- | --- | --- | --- |
| 1 | id | char(36) | NO |  | PK |  |
| 2 | period_id | char(36) | NO |  | UK:finance_depreciation_runs_unique_run; FK -> finance_periods.id |  |
| 3 | run_no | int(10) UNSIGNED | NO |  | UK:finance_depreciation_runs_unique_run |  |
| 4 | status | enum('DRAFT','POSTED','VOID') | NO | 'DRAFT' | IDX |  |
| 5 | assets_count | int(10) UNSIGNED | NO | 0 |  |  |
| 6 | total_depreciation | decimal(18,2) | NO | 0.00 |  |  |
| 7 | generated_by | char(36) | YES | NULL | IDX; FK -> users.id |  |
| 8 | generated_at | timestamp | NO | current_timestamp() | IDX |  |
| 9 | notes | text | YES | NULL |  |  |
| 10 | created_at | timestamp | YES | NULL |  |  |
| 11 | updated_at | timestamp | YES | NULL |  |  |

### Tabel finance_general_ledger_batches

| Table Name | finance_general_ledger_batches |
| --- | --- |
| Modul | Finance |
| Keterangan | Batch import general ledger. |
| Jumlah Kolom | 13 |

| No | Column Name | Data Type | Nullable | Default | Key | Extra |
| ---: | --- | --- | --- | --- | --- | --- |
| 1 | id | char(36) | NO |  | PK |  |
| 2 | source_type | enum('IMPORT','MANUAL') | NO | 'IMPORT' | IDX |  |
| 3 | batch_name | varchar(255) | NO |  |  |  |
| 4 | source_filename | varchar(255) | YES | NULL |  |  |
| 5 | sheet_name | varchar(120) | YES | NULL |  |  |
| 6 | imported_year | smallint(5) UNSIGNED | YES | NULL | IDX |  |
| 7 | notes | text | YES | NULL |  |  |
| 8 | meta | longtext | YES | NULL |  |  |
| 9 | imported_at | timestamp | YES | NULL | IDX |  |
| 10 | created_by | char(36) | YES | NULL | IDX; FK -> users.id |  |
| 11 | updated_by | char(36) | YES | NULL | IDX; FK -> users.id |  |
| 12 | created_at | timestamp | YES | NULL |  |  |
| 13 | updated_at | timestamp | YES | NULL |  |  |

### Tabel finance_general_ledger_entries

| Table Name | finance_general_ledger_entries |
| --- | --- |
| Modul | Finance |
| Keterangan | Detail entri general ledger. |
| Jumlah Kolom | 25 |

| No | Column Name | Data Type | Nullable | Default | Key | Extra |
| ---: | --- | --- | --- | --- | --- | --- |
| 1 | id | char(36) | NO |  | PK |  |
| 2 | batch_id | char(36) | NO |  | IDX:finance_general_ledger_entries_batch_id_account_code_index; IDX:finance_general_ledger_entries_batch_id_entry_date_index; IDX:finance_general_ledger_entries_batch_id_row_type_index; IDX:finance_general_ledger_entries_batch_id_is_manual_index; IDX:finance_gl_entries_account_order_idx; FK -> finance_general_ledger_batches.id |  |
| 3 | row_type | enum('OPENING','ENTRY') | NO | 'ENTRY' | IDX:finance_general_ledger_entries_batch_id_row_type_index |  |
| 4 | entry_date | date | YES | NULL | IDX:finance_general_ledger_entries_batch_id_entry_date_index; IDX:finance_gl_entries_account_order_idx |  |
| 5 | account_code | varchar(64) | NO |  | IDX:finance_general_ledger_entries_batch_id_account_code_index; IDX:finance_gl_entries_account_order_idx |  |
| 6 | account_name | varchar(255) | NO |  |  |  |
| 7 | transaction_no | varchar(120) | YES | NULL |  |  |
| 8 | communication | varchar(255) | YES | NULL |  |  |
| 9 | partner_name | varchar(255) | YES | NULL |  |  |
| 10 | currency | varchar(20) | YES | NULL |  |  |
| 11 | label | varchar(255) | YES | NULL |  |  |
| 12 | reference | varchar(255) | YES | NULL |  |  |
| 13 | analytic_distribution | varchar(255) | YES | NULL |  |  |
| 14 | opening_balance | decimal(18,2) | NO | 0.00 |  |  |
| 15 | debit | decimal(18,2) | NO | 0.00 |  |  |
| 16 | credit | decimal(18,2) | NO | 0.00 |  |  |
| 17 | balance_amount | decimal(18,2) | NO | 0.00 |  |  |
| 18 | sort_order | int(10) UNSIGNED | NO | 0 | IDX:finance_gl_entries_account_order_idx |  |
| 19 | sheet_row_number | int(10) UNSIGNED | YES | NULL |  |  |
| 20 | is_manual | tinyint(1) | NO | 0 | IDX:finance_general_ledger_entries_batch_id_is_manual_index |  |
| 21 | meta | longtext | YES | NULL |  |  |
| 22 | created_by | char(36) | YES | NULL | IDX; FK -> users.id |  |
| 23 | updated_by | char(36) | YES | NULL | IDX; FK -> users.id |  |
| 24 | created_at | timestamp | YES | NULL |  |  |
| 25 | updated_at | timestamp | YES | NULL |  |  |

### Tabel finance_invoice_items

| Table Name | finance_invoice_items |
| --- | --- |
| Modul | Finance |
| Keterangan | Detail item invoice/jurnal finance. |
| Jumlah Kolom | 13 |

| No | Column Name | Data Type | Nullable | Default | Key | Extra |
| ---: | --- | --- | --- | --- | --- | --- |
| 1 | id | bigint(20) UNSIGNED | NO |  | PK | auto_increment |
| 2 | invoice_id | char(36) | NO |  | IDX:finance_invoice_items_invoice_id_sort_order_index; FK -> finance_invoices.id |  |
| 3 | asset_category | varchar(120) | YES | NULL |  |  |
| 4 | account_code | varchar(64) | NO |  | IDX |  |
| 5 | partner_name | varchar(255) | YES | NULL |  |  |
| 6 | label | varchar(255) | NO |  |  |  |
| 7 | analytic_distribution | varchar(255) | YES | NULL |  |  |
| 8 | debit | decimal(18,2) | NO | 0.00 |  |  |
| 9 | credit | decimal(18,2) | NO | 0.00 |  |  |
| 10 | sort_order | int(10) UNSIGNED | NO | 0 | IDX:finance_invoice_items_invoice_id_sort_order_index |  |
| 11 | meta | longtext | YES | NULL |  |  |
| 12 | created_at | timestamp | YES | NULL |  |  |
| 13 | updated_at | timestamp | YES | NULL |  |  |

### Tabel finance_invoice_notes

| Table Name | finance_invoice_notes |
| --- | --- |
| Modul | Finance |
| Keterangan | Catatan invoice finance. |
| Jumlah Kolom | 6 |

| No | Column Name | Data Type | Nullable | Default | Key | Extra |
| ---: | --- | --- | --- | --- | --- | --- |
| 1 | id | bigint(20) UNSIGNED | NO |  | PK | auto_increment |
| 2 | invoice_id | char(36) | NO |  | IDX:finance_invoice_notes_invoice_id_created_at_index; FK -> finance_invoices.id |  |
| 3 | user_id | char(36) | YES | NULL | IDX; FK -> users.id |  |
| 4 | note | text | NO |  |  |  |
| 5 | created_at | timestamp | YES | NULL | IDX:finance_invoice_notes_invoice_id_created_at_index |  |
| 6 | updated_at | timestamp | YES | NULL |  |  |

### Tabel finance_invoices

| Table Name | finance_invoices |
| --- | --- |
| Modul | Finance |
| Keterangan | Header invoice/jurnal finance. |
| Jumlah Kolom | 16 |

| No | Column Name | Data Type | Nullable | Default | Key | Extra |
| ---: | --- | --- | --- | --- | --- | --- |
| 1 | id | char(36) | NO |  | PK |  |
| 2 | invoice_no | varchar(64) | NO |  | UK |  |
| 3 | accounting_date | date | NO |  | IDX |  |
| 4 | entry_type | enum('INCOME','EXPENSE') | NO |  | IDX |  |
| 5 | journal_name | varchar(255) | NO |  |  |  |
| 6 | reference | varchar(255) | YES | NULL |  |  |
| 7 | status | enum('DRAFT','POSTED','CANCELLED') | NO | 'DRAFT' | IDX |  |
| 8 | total_debit | decimal(18,2) | NO | 0.00 |  |  |
| 9 | total_credit | decimal(18,2) | NO | 0.00 |  |  |
| 10 | created_by | char(36) | YES | NULL | IDX; FK -> users.id |  |
| 11 | updated_by | char(36) | YES | NULL | IDX; FK -> users.id |  |
| 12 | posted_by | char(36) | YES | NULL | IDX; FK -> users.id |  |
| 13 | posted_at | timestamp | YES | NULL |  |  |
| 14 | meta | longtext | YES | NULL |  |  |
| 15 | created_at | timestamp | YES | NULL |  |  |
| 16 | updated_at | timestamp | YES | NULL |  |  |

### Tabel finance_journal_entries

| Table Name | finance_journal_entries |
| --- | --- |
| Modul | Finance |
| Keterangan | Jurnal finance ringkas. |
| Jumlah Kolom | 11 |

| No | Column Name | Data Type | Nullable | Default | Key | Extra |
| ---: | --- | --- | --- | --- | --- | --- |
| 1 | id | char(36) | NO |  | PK |  |
| 2 | period_id | char(36) | NO |  | IDX:finance_journal_entries_period_id_entry_type_index; FK -> finance_periods.id |  |
| 3 | entry_type | enum('INCOME','EXPENSE') | NO |  | UK:finance_journal_entries_unique_source; IDX:finance_journal_entries_period_id_entry_type_index |  |
| 4 | source_table | varchar(64) | NO |  | UK:finance_journal_entries_unique_source |  |
| 5 | source_id | varchar(64) | NO |  | UK:finance_journal_entries_unique_source |  |
| 6 | source_date | date | NO |  | IDX |  |
| 7 | amount | decimal(18,2) | NO |  |  |  |
| 8 | status | enum('FINAL','VOID') | NO | 'FINAL' | IDX |  |
| 9 | meta | longtext | YES | NULL |  |  |
| 10 | created_at | timestamp | YES | NULL |  |  |
| 11 | updated_at | timestamp | YES | NULL |  |  |

### Tabel finance_periods

| Table Name | finance_periods |
| --- | --- |
| Modul | Finance |
| Keterangan | Master periode finance. |
| Jumlah Kolom | 14 |

| No | Column Name | Data Type | Nullable | Default | Key | Extra |
| ---: | --- | --- | --- | --- | --- | --- |
| 1 | id | char(36) | NO |  | PK |  |
| 2 | period_type | enum('DAILY','MONTHLY','YEARLY') | NO |  | UK:finance_periods_unique_period |  |
| 3 | year | smallint(5) UNSIGNED | NO |  | UK:finance_periods_unique_period; IDX:finance_periods_year_month_index; IDX:finance_periods_idx_year_month_day |  |
| 4 | month | tinyint(3) UNSIGNED | NO | 0 | UK:finance_periods_unique_period; IDX:finance_periods_year_month_index; IDX:finance_periods_idx_year_month_day |  |
| 5 | day | tinyint(3) UNSIGNED | NO | 0 | UK:finance_periods_unique_period; IDX:finance_periods_idx_year_month_day |  |
| 6 | start_date | date | NO |  |  |  |
| 7 | end_date | date | NO |  |  |  |
| 8 | opening_balance | decimal(18,2) | NO | 0.00 |  |  |
| 9 | closing_balance | decimal(18,2) | NO | 0.00 |  |  |
| 10 | status | enum('OPEN','CLOSED','LOCKED') | NO | 'OPEN' | IDX |  |
| 11 | locked_by | char(36) | YES | NULL | IDX; FK -> users.id |  |
| 12 | locked_at | timestamp | YES | NULL |  |  |
| 13 | created_at | timestamp | YES | NULL |  |  |
| 14 | updated_at | timestamp | YES | NULL |  |  |

### Tabel finance_reconciliation_snapshots

| Table Name | finance_reconciliation_snapshots |
| --- | --- |
| Modul | Finance |
| Keterangan | Snapshot rekonsiliasi periode. |
| Jumlah Kolom | 12 |

| No | Column Name | Data Type | Nullable | Default | Key | Extra |
| ---: | --- | --- | --- | --- | --- | --- |
| 1 | id | char(36) | NO |  | PK |  |
| 2 | period_id | char(36) | NO |  | UK:finance_recon_snapshots_unique_period_run; FK -> finance_periods.id |  |
| 3 | depreciation_run_id | char(36) | NO |  | UK:finance_recon_snapshots_unique_period_run; IDX; FK -> finance_depreciation_runs.id |  |
| 4 | income_total | decimal(18,2) | NO | 0.00 |  |  |
| 5 | expense_total | decimal(18,2) | NO | 0.00 |  |  |
| 6 | depreciation_total | decimal(18,2) | NO | 0.00 |  |  |
| 7 | net_result | decimal(18,2) | NO | 0.00 |  |  |
| 8 | generated_by | char(36) | YES | NULL | IDX; FK -> users.id |  |
| 9 | generated_at | timestamp | NO | current_timestamp() | IDX |  |
| 10 | notes | text | YES | NULL |  |  |
| 11 | created_at | timestamp | YES | NULL |  |  |
| 12 | updated_at | timestamp | YES | NULL |  |  |

### Tabel finance_report_review_logs

| Table Name | finance_report_review_logs |
| --- | --- |
| Modul | Finance |
| Keterangan | Log review laporan finance. |
| Jumlah Kolom | 7 |

| No | Column Name | Data Type | Nullable | Default | Key | Extra |
| ---: | --- | --- | --- | --- | --- | --- |
| 1 | id | char(36) | NO |  | PK |  |
| 2 | report_snapshot_id | char(36) | NO |  | IDX:finance_report_review_logs_idx; FK -> finance_report_snapshots.id |  |
| 3 | reviewed_by | char(36) | YES | NULL | IDX; FK -> users.id |  |
| 4 | reviewed_at | timestamp | NO | current_timestamp() | IDX:finance_report_review_logs_idx |  |
| 5 | note | text | YES | NULL |  |  |
| 6 | created_at | timestamp | YES | NULL |  |  |
| 7 | updated_at | timestamp | YES | NULL |  |  |

### Tabel finance_report_snapshot_items

| Table Name | finance_report_snapshot_items |
| --- | --- |
| Modul | Finance |
| Keterangan | Detail item snapshot laporan finance. |
| Jumlah Kolom | 9 |

| No | Column Name | Data Type | Nullable | Default | Key | Extra |
| ---: | --- | --- | --- | --- | --- | --- |
| 1 | id | bigint(20) UNSIGNED | NO |  | PK | auto_increment |
| 2 | report_snapshot_id | char(36) | NO |  | UK:finance_report_snapshot_items_unique_line; IDX:finance_report_snapshot_items_idx_sort; FK -> finance_report_snapshots.id |  |
| 3 | line_code | varchar(64) | NO |  | UK:finance_report_snapshot_items_unique_line |  |
| 4 | line_label | varchar(255) | NO |  |  |  |
| 5 | amount | decimal(18,2) | NO |  |  |  |
| 6 | sort_order | int(10) UNSIGNED | NO | 0 | IDX:finance_report_snapshot_items_idx_sort |  |
| 7 | meta | longtext | YES | NULL |  |  |
| 8 | created_at | timestamp | YES | NULL |  |  |
| 9 | updated_at | timestamp | YES | NULL |  |  |

### Tabel finance_report_snapshots

| Table Name | finance_report_snapshots |
| --- | --- |
| Modul | Finance |
| Keterangan | Snapshot laporan finance. |
| Jumlah Kolom | 11 |

| No | Column Name | Data Type | Nullable | Default | Key | Extra |
| ---: | --- | --- | --- | --- | --- | --- |
| 1 | id | char(36) | NO |  | PK |  |
| 2 | period_id | char(36) | NO |  | UK:finance_report_snapshots_unique_version; IDX; IDX:finance_report_snapshots_report_type_period_id_index; FK -> finance_periods.id |  |
| 3 | report_type | enum('DAILY','MONTHLY','YEARLY') | NO |  | UK:finance_report_snapshots_unique_version; IDX:finance_report_snapshots_report_type_period_id_index |  |
| 4 | version_no | int(10) UNSIGNED | NO |  | UK:finance_report_snapshots_unique_version |  |
| 5 | reconciliation_snapshot_id | char(36) | NO |  | IDX; FK -> finance_reconciliation_snapshots.id |  |
| 6 | summary | longtext | NO |  |  |  |
| 7 | generated_by | char(36) | YES | NULL | IDX; FK -> users.id |  |
| 8 | generated_at | timestamp | NO | current_timestamp() |  |  |
| 9 | is_read_only | tinyint(1) | NO | 1 | IDX |  |
| 10 | created_at | timestamp | YES | NULL |  |  |
| 11 | updated_at | timestamp | YES | NULL |  |  |

### Tabel finance_statement_batches

| Table Name | finance_statement_batches |
| --- | --- |
| Modul | Finance |
| Keterangan | Batch import financial statement. |
| Jumlah Kolom | 14 |

| No | Column Name | Data Type | Nullable | Default | Key | Extra |
| ---: | --- | --- | --- | --- | --- | --- |
| 1 | id | char(36) | NO |  | PK |  |
| 2 | statement_type | enum('BALANCE_SHEET','PROFIT_LOSS') | NO |  | IDX |  |
| 3 | source_type | enum('IMPORT','MANUAL') | NO | 'IMPORT' | IDX |  |
| 4 | batch_name | varchar(255) | NO |  |  |  |
| 5 | source_filename | varchar(255) | YES | NULL |  |  |
| 6 | sheet_name | varchar(120) | YES | NULL |  |  |
| 7 | imported_year | smallint(5) UNSIGNED | YES | NULL | IDX |  |
| 8 | notes | text | YES | NULL |  |  |
| 9 | meta | longtext | YES | NULL |  |  |
| 10 | imported_at | timestamp | YES | NULL | IDX |  |
| 11 | created_by | char(36) | YES | NULL | IDX; FK -> users.id |  |
| 12 | updated_by | char(36) | YES | NULL | IDX; FK -> users.id |  |
| 13 | created_at | timestamp | YES | NULL |  |  |
| 14 | updated_at | timestamp | YES | NULL |  |  |

### Tabel finance_statement_rows

| Table Name | finance_statement_rows |
| --- | --- |
| Modul | Finance |
| Keterangan | Detail baris financial statement. |
| Jumlah Kolom | 17 |

| No | Column Name | Data Type | Nullable | Default | Key | Extra |
| ---: | --- | --- | --- | --- | --- | --- |
| 1 | id | char(36) | NO |  | PK |  |
| 2 | batch_id | char(36) | NO |  | IDX:finance_statement_rows_batch_id_section_key_index; IDX:finance_statement_rows_batch_id_account_code_index; IDX:finance_statement_rows_batch_id_is_manual_index; IDX:finance_statement_rows_batch_id_sort_order_index; FK -> finance_statement_batches.id |  |
| 3 | section_key | varchar(60) | YES | NULL | IDX:finance_statement_rows_batch_id_section_key_index |  |
| 4 | section_label | varchar(120) | YES | NULL |  |  |
| 5 | group_label | varchar(255) | YES | NULL |  |  |
| 6 | account_code | varchar(64) | YES | NULL | IDX:finance_statement_rows_batch_id_account_code_index |  |
| 7 | account_name | varchar(255) | NO |  |  |  |
| 8 | finance_type | varchar(60) | YES | NULL |  |  |
| 9 | amount | decimal(18,2) | NO | 0.00 |  |  |
| 10 | sort_order | int(10) UNSIGNED | NO | 0 | IDX:finance_statement_rows_batch_id_sort_order_index |  |
| 11 | sheet_row_number | int(10) UNSIGNED | YES | NULL |  |  |
| 12 | is_manual | tinyint(1) | NO | 0 | IDX:finance_statement_rows_batch_id_is_manual_index |  |
| 13 | meta | longtext | YES | NULL |  |  |
| 14 | created_by | char(36) | YES | NULL | IDX; FK -> users.id |  |
| 15 | updated_by | char(36) | YES | NULL | IDX; FK -> users.id |  |
| 16 | created_at | timestamp | YES | NULL |  |  |
| 17 | updated_at | timestamp | YES | NULL |  |  |

### Tabel jobs

| Table Name | jobs |
| --- | --- |
| Modul | Framework |
| Keterangan | Queue jobs Laravel. |
| Jumlah Kolom | 7 |

| No | Column Name | Data Type | Nullable | Default | Key | Extra |
| ---: | --- | --- | --- | --- | --- | --- |
| 1 | id | bigint(20) UNSIGNED | NO |  | PK | auto_increment |
| 2 | queue | varchar(255) | NO |  | IDX |  |
| 3 | payload | longtext | NO |  |  |  |
| 4 | attempts | tinyint(3) UNSIGNED | NO |  |  |  |
| 5 | reserved_at | int(10) UNSIGNED | YES | NULL |  |  |
| 6 | available_at | int(10) UNSIGNED | NO |  |  |  |
| 7 | created_at | int(10) UNSIGNED | NO |  |  |  |

### Tabel login_histories

| Table Name | login_histories |
| --- | --- |
| Modul | Auth & User |
| Keterangan | Riwayat login pengguna. |
| Jumlah Kolom | 9 |

| No | Column Name | Data Type | Nullable | Default | Key | Extra |
| ---: | --- | --- | --- | --- | --- | --- |
| 1 | id | bigint(20) UNSIGNED | NO |  | PK | auto_increment |
| 2 | user_id | char(36) | NO |  | IDX; FK -> users.id |  |
| 3 | ip_address | varchar(45) | YES | NULL |  |  |
| 4 | user_agent | text | YES | NULL |  |  |
| 5 | session_id | varchar(255) | YES | NULL |  |  |
| 6 | locale | varchar(5) | YES | NULL |  |  |
| 7 | logged_in_at | timestamp | YES | NULL |  |  |
| 8 | created_at | timestamp | YES | NULL |  |  |
| 9 | updated_at | timestamp | YES | NULL |  |  |

### Tabel maintenance_documentations

| Table Name | maintenance_documentations |
| --- | --- |
| Modul | Asset & Maintenance |
| Keterangan | Dokumentasi file untuk maintenance log. |
| Jumlah Kolom | 5 |

| No | Column Name | Data Type | Nullable | Default | Key | Extra |
| ---: | --- | --- | --- | --- | --- | --- |
| 1 | id | char(36) | NO |  | PK |  |
| 2 | maintenance_log_id | char(36) | NO |  | IDX; FK -> maintenance_logs.id |  |
| 3 | document_path | varchar(255) | NO |  |  |  |
| 4 | created_at | timestamp | YES | NULL |  |  |
| 5 | updated_at | timestamp | YES | NULL |  |  |

### Tabel maintenance_logs

| Table Name | maintenance_logs |
| --- | --- |
| Modul | Asset & Maintenance |
| Keterangan | Log laporan/perawatan aset. |
| Jumlah Kolom | 11 |

| No | Column Name | Data Type | Nullable | Default | Key | Extra |
| ---: | --- | --- | --- | --- | --- | --- |
| 1 | id | char(36) | NO |  | PK |  |
| 2 | asset_id | char(36) | NO |  | IDX; FK -> assets.id |  |
| 3 | worker_name | varchar(255) | NO |  |  |  |
| 4 | date | date | NO |  |  |  |
| 5 | issue_description | text | NO |  |  |  |
| 6 | working_description | text | NO |  |  |  |
| 7 | pic | varchar(255) | NO |  |  |  |
| 8 | cost | decimal(15,2) | NO | 0.00 |  |  |
| 9 | status | enum('Pending','Approved','Rejected') | NO | 'Pending' |  |  |
| 10 | created_at | timestamp | YES | NULL |  |  |
| 11 | updated_at | timestamp | YES | NULL |  |  |

### Tabel maintenance_notification_recipients

| Table Name | maintenance_notification_recipients |
| --- | --- |
| Modul | Asset & Maintenance |
| Keterangan | Daftar penerima email notifikasi maintenance. |
| Jumlah Kolom | 6 |
| Catatan | Created by 2026_05_23_110100_create_maintenance_notification_recipients_table. |

| No | Column Name | Data Type | Nullable | Default | Key | Extra |
| ---: | --- | --- | --- | --- | --- | --- |
| 1 | id | char(36) | NO |  | PK |  |
| 2 | name | varchar(120) | YES | NULL |  |  |
| 3 | email | varchar(255) | NO |  | UK |  |
| 4 | created_by | char(36) | YES | NULL | FK -> users.id |  |
| 5 | created_at | timestamp | YES | NULL | IDX |  |
| 6 | updated_at | timestamp | YES | NULL |  |  |

### Tabel message_attachments

| Table Name | message_attachments |
| --- | --- |
| Modul | Announcement & Notification |
| Keterangan | Lampiran pesan/pengumuman. |
| Jumlah Kolom | 9 |

| No | Column Name | Data Type | Nullable | Default | Key | Extra |
| ---: | --- | --- | --- | --- | --- | --- |
| 1 | id | bigint(20) UNSIGNED | NO |  | PK | auto_increment |
| 2 | attachable_type | varchar(255) | YES | NULL | IDX:message_attachments_attachable_type_attachable_id_index |  |
| 3 | attachable_id | bigint(20) UNSIGNED | YES | NULL | IDX:message_attachments_attachable_type_attachable_id_index |  |
| 4 | file_path | varchar(255) | NO |  |  |  |
| 5 | original_name | varchar(255) | NO |  |  |  |
| 6 | mime_type | varchar(255) | NO |  |  |  |
| 7 | size | bigint(20) UNSIGNED | NO |  |  |  |
| 8 | created_at | timestamp | YES | NULL |  |  |
| 9 | updated_at | timestamp | YES | NULL |  |  |

### Tabel migrations

| Table Name | migrations |
| --- | --- |
| Modul | Framework |
| Keterangan | Riwayat migration Laravel. |
| Jumlah Kolom | 3 |

| No | Column Name | Data Type | Nullable | Default | Key | Extra |
| ---: | --- | --- | --- | --- | --- | --- |
| 1 | id | int(10) UNSIGNED | NO |  | PK | auto_increment |
| 2 | migration | varchar(255) | NO |  |  |  |
| 3 | batch | int(11) | NO |  |  |  |

### Tabel parent_users

| Table Name | parent_users |
| --- | --- |
| Modul | Parent |
| Keterangan | Master wali/orang tua. |
| Jumlah Kolom | 7 |

| No | Column Name | Data Type | Nullable | Default | Key | Extra |
| ---: | --- | --- | --- | --- | --- | --- |
| 1 | id | bigint(20) UNSIGNED | NO |  | PK | auto_increment |
| 2 | name | varchar(255) | NO |  |  |  |
| 3 | email | varchar(255) | NO |  | UK |  |
| 4 | phone | varchar(255) | NO |  | UK |  |
| 5 | address | varchar(255) | YES | NULL |  |  |
| 6 | created_at | timestamp | YES | NULL |  |  |
| 7 | updated_at | timestamp | YES | NULL |  |  |

### Tabel password_reset_tokens

| Table Name | password_reset_tokens |
| --- | --- |
| Modul | Auth & User |
| Keterangan | Token reset password. |
| Jumlah Kolom | 3 |

| No | Column Name | Data Type | Nullable | Default | Key | Extra |
| ---: | --- | --- | --- | --- | --- | --- |
| 1 | email | varchar(255) | NO |  | PK |  |
| 2 | token | varchar(255) | NO |  |  |  |
| 3 | created_at | timestamp | YES | NULL |  |  |

### Tabel payments

| Table Name | payments |
| --- | --- |
| Modul | Billing & Tunggakan |
| Keterangan | Pembayaran tagihan. |
| Jumlah Kolom | 9 |

| No | Column Name | Data Type | Nullable | Default | Key | Extra |
| ---: | --- | --- | --- | --- | --- | --- |
| 1 | id | bigint(20) UNSIGNED | NO |  | PK | auto_increment |
| 2 | billing_id | bigint(20) UNSIGNED | NO |  | IDX; FK -> billings.id |  |
| 3 | method | enum('MANUAL_TRANSFER') | NO |  |  |  |
| 4 | status | enum('PENDING','CONFIRMED','REJECTED') | NO | 'PENDING' |  |  |
| 5 | proof_path | varchar(255) | YES | NULL |  |  |
| 6 | confirmed_by | char(36) | YES | NULL | IDX; FK -> users.id |  |
| 7 | confirmed_at | timestamp | YES | NULL |  |  |
| 8 | created_at | timestamp | YES | NULL |  |  |
| 9 | updated_at | timestamp | YES | NULL |  |  |

### Tabel personal_access_tokens

| Table Name | personal_access_tokens |
| --- | --- |
| Modul | Auth & User |
| Keterangan | Token API Sanctum. |
| Jumlah Kolom | 10 |

| No | Column Name | Data Type | Nullable | Default | Key | Extra |
| ---: | --- | --- | --- | --- | --- | --- |
| 1 | id | bigint(20) UNSIGNED | NO |  | PK | auto_increment |
| 2 | tokenable_type | varchar(255) | NO |  | IDX:personal_access_tokens_tokenable_type_tokenable_id_index |  |
| 3 | tokenable_id | bigint(20) UNSIGNED | NO |  | IDX:personal_access_tokens_tokenable_type_tokenable_id_index |  |
| 4 | name | varchar(255) | NO |  |  |  |
| 5 | token | varchar(64) | NO |  | UK |  |
| 6 | abilities | text | YES | NULL |  |  |
| 7 | last_used_at | timestamp | YES | NULL |  |  |
| 8 | expires_at | timestamp | YES | NULL |  |  |
| 9 | created_at | timestamp | YES | NULL |  |  |
| 10 | updated_at | timestamp | YES | NULL |  |  |

### Tabel recipent_data_koperasi_tirta_jatik_utama

| Table Name | recipent_data_koperasi_tirta_jatik_utama |
| --- | --- |
| Modul | Blast & Recipient |
| Keterangan | Master penerima koperasi Tirta Jatik Utama. |
| Jumlah Kolom | 12 |

| No | Column Name | Data Type | Nullable | Default | Key | Extra |
| ---: | --- | --- | --- | --- | --- | --- |
| 1 | id | char(36) | NO |  | PK |  |
| 2 | nama_karyawan | varchar(255) | NO |  |  |  |
| 3 | instansi | varchar(255) | YES | NULL | IDX |  |
| 4 | nama_wali | varchar(255) | YES | NULL |  |  |
| 5 | wa_karyawan | varchar(255) | YES | NULL | IDX |  |
| 6 | email_karyawan | varchar(255) | YES | NULL | IDX |  |
| 7 | catatan | text | YES | NULL |  |  |
| 8 | source | varchar(255) | YES | NULL |  |  |
| 9 | is_valid | tinyint(1) | NO | 0 | IDX |  |
| 10 | validation_error | text | YES | NULL |  |  |
| 11 | created_at | timestamp | YES | NULL |  |  |
| 12 | updated_at | timestamp | YES | NULL |  |  |

### Tabel reminders

| Table Name | reminders |
| --- | --- |
| Modul | Announcement & Notification |
| Keterangan | Pengingat terkait pengumuman/kegiatan. |
| Jumlah Kolom | 13 |

| No | Column Name | Data Type | Nullable | Default | Key | Extra |
| ---: | --- | --- | --- | --- | --- | --- |
| 1 | id | bigint(20) UNSIGNED | NO |  | PK | auto_increment |
| 2 | title | varchar(255) | NO |  |  |  |
| 3 | description | text | YES | NULL |  |  |
| 4 | remind_at | datetime | NO |  | IDX:reminders_is_active_remind_at_index |  |
| 5 | alert_before_minutes | int(10) UNSIGNED | NO | 30 |  |  |
| 6 | type | enum('GENERAL','ANNOUNCEMENT') | NO | 'GENERAL' | IDX |  |
| 7 | announcement_id | bigint(20) UNSIGNED | YES | NULL | IDX; FK -> announcements.id |  |
| 8 | is_active | tinyint(1) | NO | 1 | IDX:reminders_is_active_remind_at_index |  |
| 9 | created_by | char(36) | NO |  | IDX; FK -> users.id |  |
| 10 | deactivated_at | timestamp | YES | NULL |  |  |
| 11 | deactivated_by | char(36) | YES | NULL | IDX; FK -> users.id |  |
| 12 | created_at | timestamp | YES | NULL |  |  |
| 13 | updated_at | timestamp | YES | NULL |  |  |

### Tabel tunggakan_blast_logs

| Table Name | tunggakan_blast_logs |
| --- | --- |
| Modul | Billing & Tunggakan |
| Keterangan | Ringkasan eksekusi blast tunggakan. |
| Jumlah Kolom | 16 |

| No | Column Name | Data Type | Nullable | Default | Key | Extra |
| ---: | --- | --- | --- | --- | --- | --- |
| 1 | id | char(36) | NO |  | PK |  |
| 2 | blast_message_id | char(36) | YES | NULL | IDX; FK -> blast_messages.id |  |
| 3 | triggered_by | char(36) | YES | NULL | IDX; FK -> users.id |  |
| 4 | total_candidate_records | int(10) UNSIGNED | NO | 0 |  |  |
| 5 | total_candidate_groups | int(10) UNSIGNED | NO | 0 |  |  |
| 6 | total_processed_groups | int(10) UNSIGNED | NO | 0 |  |  |
| 7 | total_sent_groups | int(10) UNSIGNED | NO | 0 |  |  |
| 8 | total_failed_groups | int(10) UNSIGNED | NO | 0 |  |  |
| 9 | total_skipped_groups | int(10) UNSIGNED | NO | 0 |  |  |
| 10 | total_targets | int(10) UNSIGNED | NO | 0 |  |  |
| 11 | total_sent_targets | int(10) UNSIGNED | NO | 0 |  |  |
| 12 | total_failed_targets | int(10) UNSIGNED | NO | 0 |  |  |
| 13 | total_queued_targets | int(10) UNSIGNED | NO | 0 |  |  |
| 14 | details | longtext | YES | NULL |  |  |
| 15 | created_at | timestamp | YES | NULL | IDX |  |
| 16 | updated_at | timestamp | YES | NULL |  |  |

### Tabel tunggakan_import_batches

| Table Name | tunggakan_import_batches |
| --- | --- |
| Modul | Billing & Tunggakan |
| Keterangan | Batch import data tunggakan. |
| Jumlah Kolom | 10 |

| No | Column Name | Data Type | Nullable | Default | Key | Extra |
| ---: | --- | --- | --- | --- | --- | --- |
| 1 | id | char(36) | NO |  | PK |  |
| 2 | source_type | enum('excel','manual','database') | NO |  | IDX |  |
| 3 | source_reference | varchar(255) | YES | NULL |  |  |
| 4 | notes | text | YES | NULL |  |  |
| 5 | imported_by | char(36) | YES | NULL | IDX; FK -> users.id |  |
| 6 | total_rows | int(10) UNSIGNED | NO | 0 |  |  |
| 7 | matched_rows | int(10) UNSIGNED | NO | 0 |  |  |
| 8 | unmatched_rows | int(10) UNSIGNED | NO | 0 |  |  |
| 9 | created_at | timestamp | YES | NULL | IDX |  |
| 10 | updated_at | timestamp | YES | NULL |  |  |

### Tabel tunggakan_records

| Table Name | tunggakan_records |
| --- | --- |
| Modul | Billing & Tunggakan |
| Keterangan | Detail record tunggakan hasil import. |
| Jumlah Kolom | 20 |

| No | Column Name | Data Type | Nullable | Default | Key | Extra |
| ---: | --- | --- | --- | --- | --- | --- |
| 1 | id | char(36) | NO |  | PK |  |
| 2 | batch_id | char(36) | YES | NULL | IDX; FK -> tunggakan_import_batches.id |  |
| 3 | no_urut | int(10) UNSIGNED | YES | NULL |  |  |
| 4 | kelas | varchar(255) | YES | NULL | IDX:tunggakan_records_nama_murid_kelas_index |  |
| 5 | nama_murid | varchar(255) | NO |  | IDX:tunggakan_records_nama_murid_kelas_index |  |
| 6 | no_telepon | varchar(255) | YES | NULL | IDX |  |
| 7 | bulan | varchar(255) | NO |  |  |  |
| 8 | nilai | decimal(15,2) | NO | 0.00 |  |  |
| 9 | recipient_source | enum('siswa','karyawan') | YES | NULL | IDX:tunggakan_records_recipient_source_recipient_id_index |  |
| 10 | recipient_id | char(36) | YES | NULL | IDX:tunggakan_records_recipient_source_recipient_id_index |  |
| 11 | match_status | enum('matched','unmatched','multiple','manual') | NO | 'unmatched' | IDX:tunggakan_records_blast_status_match_status_index |  |
| 12 | match_notes | text | YES | NULL |  |  |
| 13 | blast_status | enum('draft','queued','sent','failed') | NO | 'draft' | IDX:tunggakan_records_blast_status_match_status_index |  |
| 14 | blasted_at | timestamp | YES | NULL |  |  |
| 15 | last_blast_log_id | bigint(20) UNSIGNED | YES | NULL | IDX; FK -> blast_logs.id |  |
| 16 | raw_payload | longtext | YES | NULL |  |  |
| 17 | created_by | char(36) | YES | NULL | IDX; FK -> users.id |  |
| 18 | updated_by | char(36) | YES | NULL | IDX; FK -> users.id |  |
| 19 | created_at | timestamp | YES | NULL |  |  |
| 20 | updated_at | timestamp | YES | NULL |  |  |

### Tabel users

| Table Name | users |
| --- | --- |
| Modul | Auth & User |
| Keterangan | Data akun pengguna dan role aplikasi. |
| Jumlah Kolom | 8 |

| No | Column Name | Data Type | Nullable | Default | Key | Extra |
| ---: | --- | --- | --- | --- | --- | --- |
| 1 | id | char(36) | NO |  | PK |  |
| 2 | name | varchar(255) | NO |  |  |  |
| 3 | email | varchar(255) | NO |  | UK |  |
| 4 | password | varchar(255) | NO |  |  |  |
| 5 | role | enum('User','Admin','IT Support','Asset Manager','Finance','Pembina','Blasting','QC') | NO | 'User' |  |  |
| 6 | remember_token | varchar(100) | YES | NULL |  |  |
| 7 | created_at | timestamp | YES | NULL |  |  |
| 8 | updated_at | timestamp | YES | NULL |  |  |
