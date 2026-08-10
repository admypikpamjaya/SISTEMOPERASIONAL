# Manual Book SOY YPIK

Paket dokumentasi operasional ini dibuat dari audit route, menu, permission, controller, service, request validation, dan tampilan proyek SOY YPIK. Baseline akses mengikuti kode; override permission dan status fitur pada database deployment dapat mengubah akses efektif.

## Dokumen

| Cakupan | PDF | Halaman |
|---|---|---:|
| Seluruh Role | [manual-book-seluruh-role-soy-ypik.pdf](manual-book-seluruh-role-soy-ypik.pdf) | 32 |
| User | [manual-book-role-user-soy-ypik.pdf](manual-book-role-user-soy-ypik.pdf) | 9 |
| Admin | [manual-book-role-admin-soy-ypik.pdf](manual-book-role-admin-soy-ypik.pdf) | 11 |
| IT Support | [manual-book-role-it-support-soy-ypik.pdf](manual-book-role-it-support-soy-ypik.pdf) | 20 |
| Asset Manager | [manual-book-role-asset-manager-soy-ypik.pdf](manual-book-role-asset-manager-soy-ypik.pdf) | 12 |
| Finance | [manual-book-role-finance-soy-ypik.pdf](manual-book-role-finance-soy-ypik.pdf) | 12 |
| Pembina | [manual-book-role-pembina-soy-ypik.pdf](manual-book-role-pembina-soy-ypik.pdf) | 15 |
| Blasting | [manual-book-role-blasting-soy-ypik.pdf](manual-book-role-blasting-soy-ypik.pdf) | 10 |
| QC | [manual-book-role-qc-soy-ypik.pdf](manual-book-role-qc-soy-ypik.pdf) | 11 |
| Sistem Management | [manual-book-role-sistem-management-soy-ypik.pdf](manual-book-role-sistem-management-soy-ypik.pdf) | 23 |

Setiap manual berformat A4, berbahasa Indonesia, memiliki daftar isi, batas akses, prosedur kerja, peringatan, troubleshooting, dan checklist role. Sumber HTML yang dapat diedit tersedia di folder `source/`.

## Workbook akses role

[akses-link-dan-kredensial-role-soy-ypik.xlsx](akses-link-dan-kredensial-role-soy-ypik.xlsx) memuat sembilan role beserta URL website, URL login, halaman awal, email akun seeder, password awal, status verifikasi, tautan manual PDF, dan repository GitHub.

> **Internal terbatas:** workbook menyimpan password awal dalam plaintext. Jangan commit/push file tersebut ke repository publik atau membagikannya melalui grup umum. Password pada deployment live dapat sudah berubah; status pada workbook menjelaskan sumber dan batas verifikasinya.

## Regenerasi dan validasi

Jalankan dari root proyek:

```powershell
node docs/manual-book/generate-manual-books.js
php docs/manual-book/generate-role-access-workbook.php
```

Generator PDF tidak membutuhkan dependency npm tambahan dan menggunakan Chrome atau Edge headless. Generator Excel memakai PhpSpreadsheet yang sudah menjadi dependency proyek. Proses build memeriksa jumlah dokumen/role, struktur output, hyperlink, signature file, ukuran, jumlah halaman PDF, dan SHA-256. Hasil validasi PDF terakhir tersimpan di [manual-book-manifest.json](manual-book-manifest.json).
