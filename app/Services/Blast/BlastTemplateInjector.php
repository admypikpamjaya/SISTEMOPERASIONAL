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
        // DATA WAJIB
        $data = [
            'nama_siswa' => $recipient->nama_siswa,
            'kelas'      => $recipient->kelas,
            'nama_wali'  => $recipient->nama_wali,
            'email'      => $recipient->email_wali,
            'wa'         => $recipient->wa_wali,
        ];

        // MERGE CONTEXT (tagihan, jatuh tempo, dll)
        $data = array_merge($data, $context);

        $message = $template->content;

        // REPLACE {placeholder}
        foreach ($data as $key => $value) {
            $message = str_replace(
                '{' . $key . '}',
                (string) $value,
                $message
            );
        }

        return new BlastPayload($message);
    }
}
