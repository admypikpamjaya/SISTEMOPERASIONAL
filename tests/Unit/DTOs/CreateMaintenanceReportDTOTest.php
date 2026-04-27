<?php

namespace Tests\Unit\DTOs;

use App\DTOs\Report\CreateMaintenanceReportDTO;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\TestCase;

class CreateMaintenanceReportDTOTest extends TestCase
{
    public function test_from_array_defaults_empty_cost_to_zero(): void
    {
        $dto = CreateMaintenanceReportDTO::fromArray([
            'asset_id' => 'asset-123',
            'worker_name' => 'Teknisi',
            'working_date' => '2026-04-06',
            'issue_description' => 'AC tidak dingin',
            'working_description' => 'Pembersihan unit indoor',
            'pic' => 'Bagian Sarpras',
            'cost' => '',
            'evidence_photo' => UploadedFile::fake()->image('bukti.jpg'),
        ]);

        $this->assertSame(0.0, $dto->cost);
    }
}
