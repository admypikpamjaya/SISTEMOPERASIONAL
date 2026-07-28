<?php

namespace Tests\Unit\Services\Finance;

use App\Services\Blast\TemplateRenderer;
use App\Services\Blast\TunggakanMessageContextService;
use App\Services\Finance\TunggakanService;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class TunggakanServiceExcelParserTest extends TestCase
{
    public function test_parse_excel_row_supports_headerless_maybank_va_format(): void
    {
        $service = new TunggakanService(
            new TemplateRenderer(),
            new TunggakanMessageContextService()
        );

        $method = new ReflectionMethod($service, 'parseExcelRow');
        $method->setAccessible(true);

        $parsed = $method->invoke($service, [
            '782252627001',
            'Ahmad Nurdiansyah P',
            'Rp 1,650,000',
            '0813-1530-0203',
            null,
        ], []);

        $this->assertSame('Ahmad Nurdiansyah P', $parsed['nama_murid']);
        $this->assertSame('782252627001', $parsed['nomor_va']);
        $this->assertSame('0813-1530-0203', $parsed['no_telepon']);
        $this->assertSame('SPP Bulanan', $parsed['bulan']);
        $this->assertSame(1650000.0, $parsed['nilai']);
        $this->assertNull($parsed['kelas']);
        $this->assertNull($parsed['no_urut']);
    }

    public function test_maybank_student_name_containing_month_word_is_not_treated_as_header(): void
    {
        $service = new TunggakanService(
            new TemplateRenderer(),
            new TunggakanMessageContextService()
        );

        $method = new ReflectionMethod($service, 'isLikelyHeaderRow');
        $method->setAccessible(true);

        $isHeader = $method->invoke($service, [
            '782252627051',
            'Zephyra Bulan Dadari',
            'Rp 1,650,000',
            '0857-2270-0017',
            null,
        ]);

        $this->assertFalse($isHeader);
    }
}
