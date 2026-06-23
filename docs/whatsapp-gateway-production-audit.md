# Audit WhatsApp Gateway Production

Gateway produksi saat ini berada di `34.122.6.79` dan diakses aplikasi melalui
`https://whatsapp.pradita.website`.

## 1. Masuk ke VM

Gunakan salah satu cara berikut.

```bash
gcloud compute ssh NAMA_INSTANCE --zone ZONE_INSTANCE
```

atau:

```bash
ssh USER_SERVER@34.122.6.79
```

## 2. Temukan Folder Aplikasi yang Dijalankan

```bash
sudo find / -path '*/whatsapp-gateway/ecosystem.config.cjs' 2>/dev/null
pm2 list
pm2 describe wa-gateway
```

Perhatikan nilai `script path` dan `exec cwd` dari `pm2 describe`. Masuk ke
folder repository yang memiliki folder `whatsapp-gateway`.

## 3. Verifikasi dan Perbarui Kode

```bash
cd /PATH/KE/REPOSITORY
git status
git remote -v
git fetch origin
git rev-parse HEAD
git rev-parse origin/main
git pull --ff-only origin main
```

Nilai `HEAD` dan `origin/main` harus sama. Setelah pull:

```bash
cd whatsapp-gateway
npm ci --omit=dev
node --check src/controllers/messageController.js
node --check src/queue/worker.js
node --check src/routes/index.js
```

## 4. Jalankan Migrasi Laravel

Dari root aplikasi Laravel:

```bash
cd /PATH/KE/REPOSITORY
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan view:cache
```

## 5. Restart Gateway dan Periksa Redis

```bash
redis-cli ping
cd /PATH/KE/REPOSITORY/whatsapp-gateway
pm2 restart ecosystem.config.cjs --update-env
pm2 save
pm2 describe wa-gateway
pm2 logs wa-gateway --lines 200
```

`redis-cli ping` harus menghasilkan `PONG`. Log gateway harus memuat
`Queue worker started` dan status device `connected`.

## 6. Verifikasi Versi dan Antrean

Muat token dari `.env` gateway tanpa menampilkannya:

```bash
cd /PATH/KE/REPOSITORY/whatsapp-gateway
set -a
source .env
set +a

curl -s https://whatsapp.pradita.website/health
curl -s -H "${API_KEY_HEADER:-x-api-key}: $API_KEY" \
  https://whatsapp.pradita.website/queue/status
curl -s -H "${API_KEY_HEADER:-x-api-key}: $API_KEY" \
  https://whatsapp.pradita.website/jobs/587
```

Respons `/health` yang benar harus memiliki:

```json
{
  "deliveryMode": "direct"
}
```

Job lama seperti `587`, `588`, dan `589` dapat berstatus `waiting`, `active`,
`completed`, `failed`, atau `unknown`.

## 7. Tes Pengiriman Langsung

Perintah berikut benar-benar mengirim pesan. Ganti nomor tujuan terlebih dahulu.

```bash
curl -s -X POST \
  -H "${API_KEY_HEADER:-x-api-key}: $API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "phone": "62NOMOR_TUJUAN",
    "message": "Tes gateway direct-send",
    "deviceId": "default"
  }' \
  https://whatsapp.pradita.website/send-message
```

Respons yang benar adalah `Message sent`, memiliki `deliveryStatus: sent`, dan
memiliki `messageId`. Jika respons masih `Message queued`, PM2 masih menjalankan
kode/folder lama.

Untuk device kedua, gunakan:

```json
"deviceId": "device-20260620161503-842"
```

## 8. Diagnosis Cepat

- `/health` masih tidak memiliki `deliveryMode`: kode gateway belum ter-deploy.
- Respons masih `Message queued`: proses PM2 masih memakai kode lama.
- Redis bukan `PONG`: BullMQ tidak dapat memproses antrean lama.
- Banyak job `waiting` dan tidak ada `active`: worker gateway tidak berjalan.
- Job `failed`: baca `failedReason` dari `/jobs/{jobId}` dan log PM2.
- `Message sent` memiliki `messageId`, tetapi penerima tidak menerima: periksa
  blokir/spam/rate limit WhatsApp serta log koneksi Baileys pada device tersebut.
