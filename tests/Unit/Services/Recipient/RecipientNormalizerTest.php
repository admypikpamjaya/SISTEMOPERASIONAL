<?php

namespace Tests\Unit\Services\Recipient;

use App\Services\Recipient\RecipientNormalizer;
use PHPUnit\Framework\TestCase;

class RecipientNormalizerTest extends TestCase
{
    public function test_normalize_valid_data_with_whatsapp_conversion_and_catatan(): void
    {
        $service = new RecipientNormalizer();

        $dto = $service->normalize([
            'nama_siswa' => ' Budi ',
            'kelas' => ' 5A ',
            'nama_wali' => ' Ibu Ani ',
            'email' => '',
            'wa' => '0812-3456-7890',
            'catatan' => ' Jemput jam 15.00 ',
        ]);

        $this->assertTrue($dto->isValid);
        $this->assertSame([], $dto->errors);
        $this->assertSame('Budi', $dto->namaSiswa);
        $this->assertSame('5A', $dto->kelas);
        $this->assertSame('Ibu Ani', $dto->namaWali);
        $this->assertNull($dto->email);
        $this->assertSame('6281234567890', $dto->phone);
        $this->assertSame('Jemput jam 15.00', $dto->catatan);
    }

    public function test_normalize_rejects_missing_required_fields_and_contact(): void
    {
        $service = new RecipientNormalizer();

        $dto = $service->normalize([
            'nama_siswa' => ' ',
            'kelas' => ' ',
            'nama_wali' => ' ',
            'email' => ' ',
            'wa' => ' ',
            'catatan' => ' ',
        ]);

        $this->assertFalse($dto->isValid);
        $this->assertContains('nama_siswa wajib diisi', $dto->errors);
        $this->assertContains('kelas wajib diisi', $dto->errors);
        $this->assertContains('nama_wali wajib diisi', $dto->errors);
        $this->assertContains('email atau WhatsApp wajib diisi', $dto->errors);
    }

    public function test_normalize_rejects_invalid_email_and_whatsapp(): void
    {
        $service = new RecipientNormalizer();

        $dto = $service->normalize([
            'nama_siswa' => 'Budi',
            'kelas' => '5A',
            'nama_wali' => 'Ibu Ani',
            'email' => 'invalid-email',
            'wa' => '12ab',
        ]);

        $this->assertFalse($dto->isValid);
        $this->assertContains('format email tidak valid', $dto->errors);
        $this->assertContains('format WhatsApp tidak valid', $dto->errors);
    }

    public function test_normalize_accepts_valid_email_without_whatsapp(): void
    {
        $service = new RecipientNormalizer();

        $dto = $service->normalize([
            'nama_siswa' => 'Siti',
            'kelas' => '6B',
            'nama_wali' => 'Bapak Ali',
            'email' => 'wali@example.com',
            'wa' => '',
        ]);

        $this->assertTrue($dto->isValid);
        $this->assertSame('wali@example.com', $dto->email);
        $this->assertNull($dto->phone);
    }
}
