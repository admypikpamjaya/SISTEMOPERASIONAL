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
            $h = Str::lower(trim((string) $header));

            if ($email === null && Str::contains($h, ['email'])) $email = (string) $index;
            if ($phone === null && $this->isPhoneHeader($h)) $phone = (string) $index;
            if ($wali === null && $this->isGuardianHeader($h)) $wali = (string) $index;
            if ($siswa === null && Str::contains($h, ['siswa', 'murid'])) $siswa = (string) $index;
            if ($kelas === null && Str::contains($h, ['kelas', 'jenjang'])) $kelas = (string) $index;
        }

        return new RecipientColumnMapDTO(
            emailCol: $email,
            phoneCol: $phone,
            namaWaliCol: $wali,
            namaSiswaCol: $siswa,
            kelasCol: $kelas,
        );
    }

    private function isPhoneHeader(string $header): bool
    {
        if (Str::contains($header, ['whatsapp', 'whasapp', 'nomor hp', 'no hp', 'telepon', 'telp'])) {
            return true;
        }

        return preg_match('/\b(nowa|wa|no wa|nomor wa|no\. wa)\b/', $header) === 1;
    }

    private function isGuardianHeader(string $header): bool
    {
        if (Str::contains($header, ['email', 'whatsapp']) || preg_match('/\bwa\b/', $header) === 1) {
            return false;
        }

        return Str::contains($header, ['nama wali', 'wali', 'orang tua', 'orangtua', 'ortu']);
    }
}
