<?php

namespace Tests\Unit\Services\Finance;

use App\Services\Finance\ReconciliationService;
use PHPUnit\Framework\TestCase;

class ReconciliationServiceTest extends TestCase
{
    public function test_calculate_returns_rounded_net_result(): void
    {
        $service = new ReconciliationService();

        $result = $service->calculate(
            totalIncome: 1000.105,
            totalExpense: 200.335,
            totalDepreciation: 100.335
        );

        $this->assertSame(1000.105, $result->totalIncome);
        $this->assertSame(200.335, $result->totalExpense);
        $this->assertSame(100.335, $result->totalDepreciation);
        $this->assertSame(699.44, $result->netResult);
    }
}
