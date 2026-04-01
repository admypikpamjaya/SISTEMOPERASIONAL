<?php

namespace App\Services\Blast;

use App\Models\BlastRecipient;

class TemplateRenderer
{
    /**
     * Render template content dengan data recipient
     */
    public function render(
        string $template,
        BlastRecipient $recipient,
        array $context = []
    ): string
    {
        $primaryWhatsapp = $recipient->wa_wali ?: $recipient->wa_wali_2;
        $instansi = $recipient->kelas;
        $namaKaryawan = $recipient->nama_siswa;

        $baseData = [
            'nama_siswa' => $recipient->nama_siswa,
            'kelas' => $recipient->kelas,
            'nama_wali' => $recipient->nama_wali,
            'email' => $recipient->email_wali,
            'wa' => $primaryWhatsapp,
            'wa_2' => $recipient->wa_wali_2,
            'nama_karyawan' => $namaKaryawan,
            'instansi' => $instansi,
        ];

        $payload = array_merge($baseData, $this->normalizeContext($context));

        return preg_replace_callback(
            '/\{\{\s*([\w.]+)\s*\}\}|\{\s*([\w.]+)\s*\}/',
            static function (array $matches) use ($payload): string {
                $token = trim((string) ($matches[1] ?: $matches[2] ?: ''));

                if ($token === '' || !array_key_exists($token, $payload)) {
                    return $matches[0];
                }

                return (string) ($payload[$token] ?? '');
            },
            $template
        ) ?? $template;
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
