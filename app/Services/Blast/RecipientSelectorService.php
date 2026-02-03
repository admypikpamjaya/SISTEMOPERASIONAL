<?php

namespace App\Services\Blast;

use App\Models\BlastRecipient;
use Illuminate\Support\Collection;

class RecipientSelectorService
{
    /**
     * channel: email | whatsapp
     * return: collection of recipients (tanpa catatan)
     */
    public function getSelectable(string $channel): Collection
    {
        $query = BlastRecipient::query()
            ->where('is_valid', true);

        if ($channel === 'email') {
            $query->whereNotNull('email_wali');
        }

        if ($channel === 'whatsapp') {
            $query->whereNotNull('wa_wali');
        }

        return $query
            ->orderBy('nama_siswa')
            ->get([
                'id',
                'nama_siswa',
                'kelas',
                'nama_wali',
                'email_wali',
                'wa_wali',
            ]);
    }

    /**
     * MULTIPLE selector by IDs
     */
    public function getByIds(array $ids): Collection
    {
        return BlastRecipient::query()
            ->whereIn('id', $ids)
            ->where('is_valid', true)
            ->get([
                'id',
                'nama_siswa',
                'kelas',
                'nama_wali',
                'email_wali',
                'wa_wali',
            ]);
    }
}
