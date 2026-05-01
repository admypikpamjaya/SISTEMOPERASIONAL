<?php

namespace Tests\Unit\Services\Recipient;

use App\Services\Recipient\ContactValueNormalizer;
use App\Services\Recipient\EmployeeRecipientNormalizer;
use Tests\TestCase;

class EmployeeRecipientNormalizerTest extends TestCase
{
    public function test_normalize_can_autocomplete_email_domain_on_import(): void
    {
        config()->set('blast.import.default_email_domain', 'foundation.test');

        $service = new EmployeeRecipientNormalizer(new ContactValueNormalizer());

        $dto = $service->normalize([
            'nama_karyawan' => 'Andi',
            'email' => 'andi.staff',
            'wa' => '',
        ], true);

        $this->assertTrue($dto->isValid);
        $this->assertSame('andi.staff@foundation.test', $dto->email);
    }

    public function test_normalize_rejects_landline_number_for_employee_whatsapp(): void
    {
        $service = new EmployeeRecipientNormalizer(new ContactValueNormalizer());

        $dto = $service->normalize([
            'nama_karyawan' => 'Andi',
            'email' => '',
            'wa' => '021 4444 1111',
        ]);

        $this->assertFalse($dto->isValid);
        $this->assertContains('nomor telepon rumah tidak bisa digunakan untuk WhatsApp', $dto->errors);
    }
}
