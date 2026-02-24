<?php

namespace Tests\Unit\Services\Recipient;

use App\Services\Recipient\RecipientDetectionService;
use PHPUnit\Framework\TestCase;

class RecipientDetectionServiceTest extends TestCase
{
    public function test_detect_maps_columns_using_common_header_keywords(): void
    {
        $service = new RecipientDetectionService();

        $map = $service->detect([
            'Email Wali',
            'No WhatsApp',
            'Nama Orang Tua',
            'Nama Siswa',
            'Kelas',
        ]);

        $this->assertSame(0, (int) $map->emailCol);
        $this->assertSame(1, (int) $map->phoneCol);
        $this->assertSame(2, (int) $map->namaWaliCol);
        $this->assertSame(3, (int) $map->namaSiswaCol);
        $this->assertSame(4, (int) $map->kelasCol);
    }

    public function test_detect_keeps_first_match_when_initial_index_is_zero(): void
    {
        $service = new RecipientDetectionService();

        $map = $service->detect([
            'Email Utama',
            'Email Cadangan',
            'No WA',
            'Nomor WhatsApp',
        ]);

        $this->assertSame(0, (int) $map->emailCol);
        $this->assertSame(2, (int) $map->phoneCol);
    }
}
