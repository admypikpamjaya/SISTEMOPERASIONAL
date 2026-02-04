<?php

namespace App\Services\Blast;

use App\Models\BlastRecipient;

class PlaceholderResolver
{
    public function resolve(string $text, BlastRecipient $recipient): string
    {
        $map = [
            '{{nama_siswa}}' => $recipient->nama_siswa,
            '{{kelas}}'      => $recipient->kelas,
            '{{nama_wali}}'  => $recipient->nama_wali,
            '{{email}}'      => $recipient->email_wali,
            '{{wa}}'         => $recipient->wa_wali,
            '{{catatan}}'    => $recipient->catatan,
        ];

        return str_replace(
            array_keys($map),
            array_values($map),
            $text
        );
    }
}
