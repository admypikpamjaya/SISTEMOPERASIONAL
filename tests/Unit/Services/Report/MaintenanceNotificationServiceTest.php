<?php

namespace Tests\Unit\Services\Report;

use App\DataTransferObjects\BlastPayload;
use App\Enums\Asset\AssetCategory;
use App\Enums\Report\Maintenance\AssetMaintenanceReportStatus;
use App\Models\Asset\Asset;
use App\Models\Log\MaintenanceDocumentation;
use App\Models\Log\MaintenanceLog;
use App\Services\Blast\EmailBlastService;
use App\Services\Report\MaintenanceNotificationService;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class MaintenanceNotificationServiceTest extends TestCase
{
    public function test_it_sends_maintenance_notification_with_payload_and_attachment(): void
    {
        config()->set('services.maintenance_notification.recipient', 'Ridodwikurniawan@gmail.com');

        $documentPath = 'maintenance-documentation/test-maintenance-mail.jpg';
        $absolutePath = storage_path('app/public/' . $documentPath);
        $directory = dirname($absolutePath);

        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        file_put_contents($absolutePath, 'fake-image-content');

        $asset = new Asset();
        $asset->setRawAttributes([
            'id' => 'asset-001',
            'account_code' => '410.01.01',
            'category' => AssetCategory::AC->value,
            'location' => 'GEDUNG A',
            'purchase_year' => '2024',
        ], true);

        $log = new MaintenanceLog();
        $log->setRawAttributes([
            'id' => 'log-001',
            'asset_id' => 'asset-001',
            'worker_name' => 'Teknisi Lapangan',
            'date' => '2026-04-27',
            'issue_description' => 'AC tidak dingin',
            'working_description' => 'Pembersihan unit dan pengisian freon',
            'pic' => 'Sarpras',
            'cost' => 150000,
            'status' => AssetMaintenanceReportStatus::PENDING->value,
        ], true);

        $log->setRelation('asset', $asset);
        $log->setRelation('maintenanceDocumentations', collect([
            tap(new MaintenanceDocumentation(), function (MaintenanceDocumentation $documentation) use ($documentPath): void {
                $documentation->setRawAttributes([
                    'document_path' => $documentPath,
                ], true);
            }),
        ]));

        $emailBlastService = Mockery::mock(EmailBlastService::class);
        $emailBlastService
            ->shouldReceive('send')
            ->once()
            ->withArgs(function (string $to, string $subject, BlastPayload $payload): bool {
                $this->assertSame('Ridodwikurniawan@gmail.com', $to);
                $this->assertStringContainsString('410.01.01', $subject);
                $this->assertStringContainsString('AC tidak dingin', $payload->message);
                $this->assertStringContainsString('Teknisi Lapangan', $payload->message);
                $this->assertCount(1, $payload->attachments);

                return true;
            })
            ->andReturn(true);

        $service = new MaintenanceNotificationService($emailBlastService);

        try {
            $this->assertTrue($service->sendForLog($log));
        } finally {
            if (is_file($absolutePath)) {
                unlink($absolutePath);
            }
        }
    }

    public function test_it_throws_exception_when_email_delivery_fails(): void
    {
        $asset = new Asset();
        $asset->setRawAttributes([
            'id' => 'asset-002',
            'account_code' => '510.01.01',
            'category' => AssetCategory::OTHER->value,
            'location' => 'GEDUNG B',
        ], true);

        $log = new MaintenanceLog();
        $log->setRawAttributes([
            'id' => 'log-002',
            'asset_id' => 'asset-002',
            'worker_name' => 'Teknisi 2',
            'date' => '2026-04-27',
            'issue_description' => 'Lampu mati',
            'working_description' => 'Penggantian ballast',
            'pic' => 'Operator',
            'cost' => 0,
            'status' => AssetMaintenanceReportStatus::PENDING->value,
        ], true);

        $log->setRelation('asset', $asset);
        $log->setRelation('maintenanceDocumentations', collect());

        $emailBlastService = Mockery::mock(EmailBlastService::class);
        $emailBlastService
            ->shouldReceive('send')
            ->once()
            ->andReturn(false);

        $service = new MaintenanceNotificationService($emailBlastService);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Email notifikasi maintenance gagal dikirim.');

        $service->sendForLog($log, true);
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }
}
