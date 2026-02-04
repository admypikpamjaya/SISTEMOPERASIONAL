<?php

namespace App\Services\Blast;

use App\Models\BlastRecipient;

class TemplateRenderer
{
    /**
     * Render template content dengan data recipient
     */
    public function render(string $template, BlastRecipient $recipient): string
    {
        $replacements = [
            '{nama_siswa}' => $recipient->nama_siswa,
            '{kelas}'      => $recipient->kelas,
            '{nama_wali}'  => $recipient->nama_wali,
            '{email}'      => $recipient->email_wali,
            '{wa}'         => $recipient->wa_wali,
        ];

        return str_replace(
            array_keys($replacements),
            array_values($replacements),
            $template
        );
    }
}
