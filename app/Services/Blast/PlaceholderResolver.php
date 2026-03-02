<?php

namespace App\Services\Blast;

use App\Models\BlastRecipient;

class PlaceholderResolver
{
    public function resolve(
        string $text,
        BlastRecipient $recipient,
        array $context = []
    ): string
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

        foreach ($this->normalizeContext($context) as $key => $value) {
            $map['{{' . $key . '}}'] = (string) ($value ?? '');
            $map['{' . $key . '}'] = (string) ($value ?? '');
        }

        return str_replace(
            array_keys($map),
            array_values($map),
            $text
        );
    }

    private function normalizeContext(array $context): array
    {
        $normalized = [];

        foreach ($context as $key => $value) {
            $token = trim((string) $key);
            if ($token === '') {
                continue;
            }

            $normalized[$token] = $value;

            if (
                !str_ends_with($token, '_rupiah')
                && is_numeric($value)
            ) {
                $normalized[$token . '_rupiah'] = $this->formatRupiah((float) $value);
            }
        }

        return $normalized;
    }

    private function formatRupiah(float $amount): string
    {
        return 'Rp ' . number_format(round($amount), 0, ',', '.');
    }
}
