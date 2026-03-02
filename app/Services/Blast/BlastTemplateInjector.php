<?php

namespace App\Services\Blast;

use App\Models\BlastTemplate;
use App\Models\BlastRecipient;
use App\DataTransferObjects\BlastPayload;
use Illuminate\Support\Str;

class BlastTemplateInjector
{
    public function inject(
        BlastTemplate $template,
        BlastRecipient $recipient,
        array $context = []
    ): BlastPayload {
        $primaryWhatsapp = $recipient->wa_wali ?: $recipient->wa_wali_2;

        // DATA WAJIB
        $data = [
            'nama_siswa' => $recipient->nama_siswa,
            'kelas'      => $recipient->kelas,
            'nama_wali'  => $recipient->nama_wali,
            'email'      => $recipient->email_wali,
            'wa'         => $primaryWhatsapp,
            'wa_2'       => $recipient->wa_wali_2,
        ];

        // MERGE CONTEXT (tagihan, jatuh tempo, dll)
        $data = array_merge($data, $this->normalizeContext($context));

        $message = $template->content;

        // REPLACE {placeholder} dan {{placeholder}}
        foreach ($data as $key => $value) {
            $message = str_replace(
                '{' . $key . '}',
                (string) $value,
                $message
            );

            $message = str_replace(
                '{{' . $key . '}}',
                (string) $value,
                $message
            );
        }

        return new BlastPayload($message);
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

            if (!str_ends_with($token, '_rupiah') && is_numeric($value)) {
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
