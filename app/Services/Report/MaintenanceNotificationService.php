<?php

namespace App\Services\Report;

use App\DataTransferObjects\BlastAttachment;
use App\DataTransferObjects\BlastPayload;
use App\Enums\Asset\AssetCategory;
use App\Models\Asset\Asset;
use App\Models\Log\MaintenanceLog;
use App\Services\Blast\EmailBlastService;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class MaintenanceNotificationService
{
    private const DEFAULT_RECIPIENT = 'Ridodwikurniawan@gmail.com';

    public function __construct(
        private EmailBlastService $emailBlastService
    ) {}

    public function getRecipient(): string
    {
        return trim((string) config(
            'services.maintenance_notification.recipient',
            self::DEFAULT_RECIPIENT
        ));
    }

    public function sendForLog(
        MaintenanceLog $log,
        bool $manuallyTriggered = false
    ): bool {
        $log->loadMissing(['asset', 'maintenanceDocumentations']);

        $asset = $log->asset;
        if (!$asset instanceof Asset) {
            throw new RuntimeException('Aset untuk laporan maintenance tidak ditemukan.');
        }

        $recipient = $this->getRecipient();
        if ($recipient === '') {
            throw new RuntimeException('Email tujuan notifikasi maintenance belum dikonfigurasi.');
        }

        $subject = $this->buildSubject($log, $asset, $manuallyTriggered);
        $payload = $this->buildPayload($log, $asset, $manuallyTriggered);

        $sent = $this->emailBlastService->send($recipient, $subject, $payload);
        if (!$sent) {
            throw new RuntimeException('Email notifikasi maintenance gagal dikirim.');
        }

        Log::info('[MAINTENANCE EMAIL SENT]', [
            'maintenance_log_id' => (string) $log->id,
            'asset_id' => (string) $asset->id,
            'recipient' => $recipient,
            'mode' => $manuallyTriggered ? 'manual' : 'automatic',
        ]);

        return true;
    }

    private function buildSubject(
        MaintenanceLog $log,
        Asset $asset,
        bool $manuallyTriggered
    ): string {
        $mode = $manuallyTriggered ? 'Manual' : 'Otomatis';
        $dateLabel = $log->date?->format('d-m-Y') ?? now()->format('d-m-Y');

        return sprintf(
            '[%s] Notifikasi Maintenance Aset %s - %s',
            $mode,
            (string) $asset->account_code,
            $dateLabel
        );
    }

    private function buildPayload(
        MaintenanceLog $log,
        Asset $asset,
        bool $manuallyTriggered
    ): BlastPayload {
        $documentationUrls = $log->maintenanceDocumentations
            ->map(fn ($documentation) => $documentation->url)
            ->values()
            ->all();

        $lines = [
            'Notifikasi maintenance aset berhasil terdeteksi oleh sistem.',
            'Mode pengiriman: ' . ($manuallyTriggered ? 'Manual' : 'Otomatis'),
            'ID laporan: ' . (string) $log->id,
            'Status laporan: ' . (string) ($log->status?->value ?? 'Pending'),
            '',
            'Data aset',
            '- Kode aset: ' . (string) $asset->account_code,
            '- Kategori: ' . $this->resolveCategoryLabel($asset->category),
            '- Lokasi: ' . ((string) $asset->location !== '' ? (string) $asset->location : '-'),
            '- Tahun pembelian: ' . ((string) ($asset->purchase_year ?? '') !== '' ? (string) $asset->purchase_year : '-'),
            '- Link detail aset: ' . route('assets.detail', ['id' => $asset->id]),
            '',
            'Data maintenance',
            '- Nama pekerja: ' . (string) $log->worker_name,
            '- Tanggal pengerjaan: ' . ($log->date?->format('d-m-Y') ?? '-'),
            '- PIC: ' . (string) $log->pic,
            '- Biaya: ' . (string) $log->cost_formatted,
            '- Masalah aset: ' . (string) $log->issue_description,
            '- Deskripsi pengerjaan: ' . (string) $log->working_description,
            '',
            'Dokumentasi',
            '- Jumlah lampiran: ' . number_format($log->maintenanceDocumentations->count(), 0, ',', '.'),
        ];

        foreach ($documentationUrls as $index => $documentationUrl) {
            $lines[] = sprintf('- URL dokumentasi %d: %s', $index + 1, $documentationUrl);
        }

        $payload = new BlastPayload(implode(PHP_EOL, $lines));
        $payload
            ->setMeta('maintenance_log_id', (string) $log->id)
            ->setMeta('asset_id', (string) $asset->id)
            ->setMeta('notification_mode', $manuallyTriggered ? 'manual' : 'automatic');

        foreach ($log->maintenanceDocumentations as $index => $documentation) {
            $absolutePath = storage_path('app/public/' . ltrim((string) $documentation->document_path, '/\\'));
            if (!is_file($absolutePath)) {
                continue;
            }

            $payload->addAttachment(new BlastAttachment(
                $absolutePath,
                'dokumentasi-maintenance-' . ($index + 1) . '.jpg',
                'image/jpeg'
            ));
        }

        return $payload;
    }

    private function resolveCategoryLabel(mixed $category): string
    {
        if ($category instanceof AssetCategory) {
            return $category->label();
        }

        $value = trim((string) $category);

        return $value !== '' ? $value : '-';
    }
}
