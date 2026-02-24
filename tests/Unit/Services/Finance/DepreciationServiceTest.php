<?php

namespace Tests\Unit\Services\Finance;

use App\DTOs\Finance\DepreciationInputDTO;
use App\Services\Finance\DepreciationService;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class DepreciationServiceTest extends TestCase
{
    public function test_calculate_straight_line_returns_expected_result(): void
    {
        $service = new DepreciationService();
        $dto = new DepreciationInputDTO(
            assetId: 'asset-001',
            acquisitionCost: 1000000,
            usefulLifeMonths: 36
        );

        $result = $service->calculateStraightLine($dto);

        $this->assertSame('asset-001', $result->assetId);
        $this->assertSame(1000000.0, $result->acquisitionCost);
        $this->assertSame(36, $result->usefulLifeMonths);
        $this->assertSame(27777.78, $result->depreciationPerMonth);
    }

    public function test_calculate_straight_line_throws_for_negative_acquisition_cost(): void
    {
        $service = new DepreciationService();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Nilai perolehan tidak boleh negatif.');

        $service->calculateStraightLine(new DepreciationInputDTO(
            assetId: 'asset-001',
            acquisitionCost: -1,
            usefulLifeMonths: 12
        ));
    }

    public function test_calculate_straight_line_throws_for_non_positive_useful_life(): void
    {
        $service = new DepreciationService();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Umur bulan harus lebih besar dari 0.');

        $service->calculateStraightLine(new DepreciationInputDTO(
            assetId: 'asset-001',
            acquisitionCost: 500000,
            usefulLifeMonths: 0
        ));
    }
}
