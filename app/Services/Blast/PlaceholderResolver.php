<?php

namespace App\Services\Blast;

use App\Models\BlastRecipient;

class PlaceholderResolver
{
    public function resolve(string $text, BlastRecipient $recipient): string
    {
        $primaryWhatsapp = $recipient->wa_wali ?: $recipient->wa_wali_2;
        $instansi = $recipient->kelas;
        $namaKaryawan = $recipient->nama_siswa;

        $map = [
            '{{nama_siswa}}' => $recipient->nama_siswa,
            '{{kelas}}'      => $recipient->kelas,
            '{{nama_wali}}'  => $recipient->nama_wali,
            '{{email}}'      => $recipient->email_wali,
            '{{wa}}'         => $primaryWhatsapp,
            '{{wa_2}}'       => $recipient->wa_wali_2,
            '{{catatan}}'    => $recipient->catatan,
            '{{nama_karyawan}}' => $namaKaryawan,
            '{{instansi}}' => $instansi,
        ];

        return str_replace(
            array_keys($map),
            array_values($map),
            $text
        );
    }
}
