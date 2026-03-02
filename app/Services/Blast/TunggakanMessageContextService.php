<?php

namespace App\Services\Blast;

use App\Models\BlastRecipient;
use App\Models\TunggakanRecord;
use Illuminate\Support\Collection;

class TunggakanMessageContextService
{
    /**
     * @var array<string, array<string, mixed>>
     */
    private array $cache = [];

    /**
     * @return array<string, mixed>
     */
    public function resolveForRecipient(BlastRecipient $recipient): array
    {
        $recipientId = trim((string) $recipient->id);
        if ($recipientId === '') {
            return [];
        }

        $recipientSource = strtolower(trim((string) ($recipient->source ?? ''))) === 'karyawan'
            ? 'karyawan'
            : 'siswa';

        $cacheKey = $recipientSource . ':' . $recipientId;
        if (array_key_exists($cacheKey, $this->cache)) {
            return $this->cache[$cacheKey];
        }

        $records = $this->loadMatchedRecordsByRecipient($recipientId, $recipientSource);

        if ($records->isEmpty()) {
            $records = $this->loadMatchedRecordsByIdentity(
                namaMurid: (string) $recipient->nama_siswa,
                kelas: (string) $recipient->kelas,
                recipientSource: $recipientSource
            );
        }

        if ($records->isEmpty()) {
            $this->cache[$cacheKey] = [];
            return [];
        }

        $totalNilai = (float) $records->sum(
            fn (TunggakanRecord $record) => (float) $record->nilai
        );

        $bulanTunggakan = $records->pluck('bulan')
            ->map(fn ($bulan) => trim((string) $bulan))
            ->filter()
            ->unique()
            ->values()
            ->implode(', ');

        $context = [
            'bulan_tunggakan' => $bulanTunggakan !== '' ? $bulanTunggakan : '-',
            'nilai_tunggakan' => $totalNilai,
            'nilai_tunggakan_rupiah' => $this->formatRupiah($totalNilai),
            'total_tunggakan' => $totalNilai,
            'total_tunggakan_rupiah' => $this->formatRupiah($totalNilai),
            'jumlah_item_tunggakan' => $records->count(),
            // Alias kompatibilitas template lama.
            'tagihan' => $totalNilai,
            'tagihan_rupiah' => $this->formatRupiah($totalNilai),
            'tunggakan' => $totalNilai,
            'tunggakan_rupiah' => $this->formatRupiah($totalNilai),
        ];

        $this->cache[$cacheKey] = $context;

        return $context;
    }

    /**
     * @return Collection<int, TunggakanRecord>
     */
    private function loadMatchedRecordsByRecipient(
        string $recipientId,
        string $recipientSource
    ): Collection {
        return TunggakanRecord::query()
            ->where('match_status', 'matched')
            ->where('recipient_source', $recipientSource)
            ->where('recipient_id', $recipientId)
            ->orderByDesc('updated_at')
            ->get();
    }

    /**
     * @return Collection<int, TunggakanRecord>
     */
    private function loadMatchedRecordsByIdentity(
        string $namaMurid,
        string $kelas,
        string $recipientSource
    ): Collection {
        $normalizedNama = strtolower(trim($namaMurid));
        $normalizedKelas = strtolower(trim($kelas));

        if ($normalizedNama === '') {
            return collect();
        }

        return TunggakanRecord::query()
            ->where('match_status', 'matched')
            ->where(function ($query) use ($recipientSource) {
                $query->where('recipient_source', $recipientSource)
                    ->orWhereNull('recipient_source');
            })
            ->whereRaw('LOWER(TRIM(nama_murid)) = ?', [$normalizedNama])
            ->when(
                $normalizedKelas !== '',
                fn ($query) => $query->whereRaw('LOWER(TRIM(kelas)) = ?', [$normalizedKelas])
            )
            ->orderByDesc('updated_at')
            ->get();
    }

    private function formatRupiah(float $amount): string
    {
        return 'Rp ' . number_format(round($amount), 0, ',', '.');
    }
}
