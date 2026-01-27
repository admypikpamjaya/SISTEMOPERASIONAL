<?php

namespace App\Services\Recipient;

use App\Models\BlastRecipient;
use Illuminate\Database\Eloquent\Collection;

class RecipientQueryService
{
    /**
     * Recipient SIAP BLAST WA
     */
    public function forWhatsapp(): Collection
    {
        return BlastRecipient::query()
            ->whereNotNull('wa_wali')
            ->where('is_valid', true)
            ->whereNotNull('nama_siswa')
            ->get();
    }

    /**
     * Recipient SIAP BLAST EMAIL
     */
    public function forEmail(): Collection
    {
        return BlastRecipient::query()
            ->whereNotNull('email_wali')
            ->where('is_valid', true)
            ->whereNotNull('nama_siswa')
            ->get();
    }
}
