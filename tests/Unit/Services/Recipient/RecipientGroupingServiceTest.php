<?php

namespace Tests\Unit\Services\Recipient;

use App\Services\Recipient\RecipientGroupingService;
use Carbon\Carbon;
use Tests\TestCase;

class RecipientGroupingServiceTest extends TestCase
{
    public function test_it_infers_education_level_from_class_name(): void
    {
        $service = new RecipientGroupingService();

        $this->assertSame('TK', $service->inferEducationLevel('TK B'));
        $this->assertSame('SD', $service->inferEducationLevel('4A'));
        $this->assertSame('SMP', $service->inferEducationLevel('8 B'));
        $this->assertSame('SMA', $service->inferEducationLevel('XI IPA'));
        $this->assertSame('SMK', $service->inferEducationLevel('SMK 12 TKJ'));
        $this->assertSame('OTHER', $service->inferEducationLevel('Kelompok Khusus'));
        $this->assertNull($service->inferEducationLevel(null));
    }

    public function test_academic_year_changes_in_july(): void
    {
        $service = new RecipientGroupingService();

        Carbon::setTestNow('2026-06-30 12:00:00');
        $this->assertSame('2025/2026', $service->currentAcademicYear());

        Carbon::setTestNow('2026-07-01 12:00:00');
        $this->assertSame('2026/2027', $service->currentAcademicYear());

        Carbon::setTestNow();
    }
}
