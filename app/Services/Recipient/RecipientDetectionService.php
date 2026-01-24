<?php

namespace App\Services\Recipient;

use App\DataTransferObjects\Recipient\RecipientColumnMapDTO;
use Illuminate\Support\Str;

class RecipientDetectionService
{
    public function detect(array $headerRow): RecipientColumnMapDTO
    {
        $email = $phone = $wali = $siswa = $kelas = null;

        foreach ($headerRow as $index => $header) {
            $h = Str::lower(trim($header));

            if (!$email && Str::contains($h, ['email'])) $email = $index;
            if (!$phone && Str::contains($h, ['wa','whatsapp','no'])) $phone = $index;
            if (!$wali && Str::contains($h, ['wali','orang tua'])) $wali = $index;
            if (!$siswa && Str::contains($h, ['siswa','murid'])) $siswa = $index;
            if (!$kelas && Str::contains($h, ['kelas','jenjang'])) $kelas = $index;
        }

        return new RecipientColumnMapDTO(
            emailCol: $email,
            phoneCol: $phone,
            namaWaliCol: $wali,
            namaSiswaCol: $siswa,
            kelasCol: $kelas,
        );
    }
}
