<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\BlastMessageTemplate;
use App\Models\TunggakanRecord;
use App\Services\Finance\TunggakanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class FinanceTunggakanController extends Controller
{
    public function __construct(
        private readonly TunggakanService $tunggakanService
    ) {}

    public function index(Request $request)
    {
        try {
            $this->tunggakanService->refreshUnmatchedMatches();
        } catch (Throwable $exception) {
            report($exception);
        }

        $filters = $request->validate([
            'q' => 'nullable|string|max:255',
            'kelas' => 'nullable|string|max:100',
            'source_type' => 'nullable|in:all,excel,manual,database',
            'match_status' => 'nullable|in:all,matched,unmatched,multiple,manual',
            'blast_status' => 'nullable|in:all,draft,queued,sent,failed',
            'per_page' => 'nullable|integer|min:20|max:200',
            'edit' => 'nullable|uuid',
        ]);

        $search = trim((string) ($filters['q'] ?? ''));
        $selectedClass = trim((string) ($filters['kelas'] ?? ''));
        $sourceType = strtolower((string) ($filters['source_type'] ?? 'all'));
        $matchStatus = strtolower((string) ($filters['match_status'] ?? 'all'));
        $blastStatus = strtolower((string) ($filters['blast_status'] ?? 'all'));
        $perPage = (int) ($filters['per_page'] ?? 50);
        if (!in_array($perPage, [20, 50, 100, 200], true)) {
            $perPage = 50;
        }

        $query = TunggakanRecord::query()
            ->with('batch:id,source_type,source_reference')
            ->latest('updated_at');

        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder->where('nama_murid', 'like', '%' . $search . '%')
                    ->orWhere('no_telepon', 'like', '%' . $search . '%')
                    ->orWhere('kelas', 'like', '%' . $search . '%')
                    ->orWhere('bulan', 'like', '%' . $search . '%')
                    ->orWhere('match_notes', 'like', '%' . $search . '%');
            });
        }

        if ($selectedClass !== '') {
            $query->where('kelas', $selectedClass);
        }

        if ($sourceType !== 'all') {
            $query->whereHas('batch', function ($builder) use ($sourceType): void {
                $builder->where('source_type', $sourceType);
            });
        }

        if ($matchStatus !== 'all') {
            $query->where('match_status', $matchStatus);
        }

        if ($blastStatus !== 'all') {
            $query->where('blast_status', $blastStatus);
        }

        $records = $query
            ->paginate($perPage)
            ->withQueryString();

        $statsQuery = TunggakanRecord::query();
        $totalRecords = (int) (clone $statsQuery)->count();
        $totalNilai = (float) ((clone $statsQuery)->sum('nilai') ?? 0);
        $matchedRecords = (int) (clone $statsQuery)
            ->where('match_status', 'matched')
            ->count();
        $needsReviewRecords = (int) (clone $statsQuery)
            ->whereIn('match_status', ['unmatched', 'multiple'])
            ->count();
        $blastSentRecords = (int) (clone $statsQuery)
            ->where('blast_status', 'sent')
            ->count();

        $editRecord = null;
        if (!empty($filters['edit'])) {
            $editRecord = TunggakanRecord::query()->find($filters['edit']);
        }

        $whatsappTemplates = BlastMessageTemplate::query()
            ->where('channel', 'whatsapp')
            ->where('is_active', true)
            ->orderByRaw("CASE WHEN name = 'Tunggakan WA Otomatis' THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->get(['id', 'name']);

        $kelasOptions = TunggakanRecord::query()
            ->select('kelas')
            ->whereNotNull('kelas')
            ->where('kelas', '!=', '')
            ->distinct()
            ->orderBy('kelas')
            ->pluck('kelas');

        return view('finance.tunggakan.index', [
            'records' => $records,
            'editRecord' => $editRecord,
            'whatsappTemplates' => $whatsappTemplates,
            'kelasOptions' => $kelasOptions,
            'filters' => [
                'q' => $search,
                'kelas' => $selectedClass,
                'source_type' => $sourceType,
                'match_status' => $matchStatus,
                'blast_status' => $blastStatus,
                'per_page' => $perPage,
            ],
            'stats' => [
                'total_records' => $totalRecords,
                'total_nilai' => $totalNilai,
                'matched_records' => $matchedRecords,
                'needs_review_records' => $needsReviewRecords,
                'blast_sent_records' => $blastSentRecords,
            ],
            'defaultSyncMonth' => now('Asia/Jakarta')->locale('id')->translatedFormat('F Y'),
            'recordsVersion' => $this->buildRecordsVersion(),
        ]);
    }

    public function version(): JsonResponse
    {
        try {
            $summary = $this->tunggakanService->refreshUnmatchedMatches(limit: 300);

            return response()->json([
                'ok' => true,
                'version' => $this->buildRecordsVersion(),
                'updated' => (int) ($summary['updated'] ?? 0),
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'ok' => false,
                'version' => $this->buildRecordsVersion(),
                'updated' => 0,
            ], 500);
        }
    }

    public function blastWhatsapp(Request $request)
    {
        $validated = $request->validate([
            'template_id' => 'nullable|string|max:50',
        ]);

        try {
            $summary = $this->tunggakanService->blastWhatsappFromTunggakan(
                templateId: $validated['template_id'] ?? null,
                actorId: auth()->id() ? (string) auth()->id() : null
            );

            if ((int) ($summary['candidate_records'] ?? 0) === 0) {
                return redirect()
                    ->route('finance.tunggakan.index')
                    ->with('success', 'Tidak ada data tunggakan draft/failed yang siap di-blast.');
            }

            return redirect()
                ->route('finance.tunggakan.index')
                ->with(
                    'success',
                    'Blast WA tunggakan selesai. '
                    . 'Recipient diproses: ' . $summary['processed_recipients']
                    . ', Recipient terkirim: ' . $summary['sent_recipients']
                    . ', Recipient gagal: ' . $summary['failed_recipients']
                    . ', Recipient skip: ' . $summary['skipped_recipients']
                    . ', Target terkirim: ' . $summary['sent_targets']
                    . ', Target gagal: ' . $summary['failed_targets']
                    . '.'
                );
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('finance.tunggakan.index')
                ->with('error', 'Blast WA tunggakan gagal: ' . $exception->getMessage());
        }
    }

    public function destroyAll()
    {
        try {
            $summary = $this->tunggakanService->deleteAllRecords();

            return redirect()
                ->route('finance.tunggakan.index')
                ->with(
                    'success',
                    'Delete all selesai. Data terhapus: '
                    . $summary['deleted_records']
                    . ' record, '
                    . $summary['deleted_batches']
                    . ' batch.'
                );
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('finance.tunggakan.index')
                ->with('error', 'Delete all tagihan gagal.');
        }
    }

    public function storeManual(Request $request)
    {
        $validated = $request->validate([
            'no_urut' => 'nullable|integer|min:1|max:999999',
            'kelas' => 'nullable|string|max:100',
            'nama_murid' => 'required|string|max:255',
            'no_telepon' => 'nullable|string|max:30',
            'bulan' => 'required|string|max:100',
            'nilai' => 'required|string|max:50',
        ]);

        try {
            $this->tunggakanService->createManualRecord([
                'no_urut' => $validated['no_urut'] ?? null,
                'kelas' => $validated['kelas'] ?? null,
                'nama_murid' => $validated['nama_murid'],
                'no_telepon' => $validated['no_telepon'] ?? null,
                'bulan' => $validated['bulan'],
                'nilai' => $validated['nilai'],
            ], auth()->id() ? (string) auth()->id() : null);

            return redirect()
                ->route('finance.tunggakan.index')
                ->with('success', 'Data tunggakan berhasil ditambahkan.');
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan data tunggakan.');
        }
    }

    public function update(Request $request, TunggakanRecord $record)
    {
        $validated = $request->validate([
            'no_urut' => 'nullable|integer|min:1|max:999999',
            'kelas' => 'nullable|string|max:100',
            'nama_murid' => 'required|string|max:255',
            'no_telepon' => 'nullable|string|max:30',
            'bulan' => 'required|string|max:100',
            'nilai' => 'required|string|max:50',
        ]);

        try {
            $this->tunggakanService->updateRecord(
                record: $record,
                payload: [
                    'no_urut' => $validated['no_urut'] ?? null,
                    'kelas' => $validated['kelas'] ?? null,
                    'nama_murid' => $validated['nama_murid'],
                    'no_telepon' => $validated['no_telepon'] ?? null,
                    'bulan' => $validated['bulan'],
                    'nilai' => $validated['nilai'],
                ],
                actorId: auth()->id() ? (string) auth()->id() : null
            );

            return redirect()
                ->route('finance.tunggakan.index')
                ->with('success', 'Data tunggakan berhasil diperbarui.');
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui data tunggakan.');
        }
    }

    public function destroy(TunggakanRecord $record)
    {
        try {
            $this->tunggakanService->deleteRecord($record);

            return redirect()
                ->route('finance.tunggakan.index')
                ->with('success', 'Data tunggakan berhasil dihapus.');
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('finance.tunggakan.index')
                ->with('error', 'Gagal menghapus data tunggakan.');
        }
    }

    public function importExcel(Request $request)
    {
        $validated = $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        $uploadedFile = $request->file('file');
        if ($uploadedFile === null) {
            return redirect()
                ->route('finance.tunggakan.index')
                ->with('error', 'File import tidak ditemukan.');
        }

        try {
            $summary = $this->tunggakanService->importFromExcel(
                $uploadedFile->getPathname(),
                $uploadedFile->getClientOriginalName(),
                auth()->id() ? (string) auth()->id() : null
            );

            $redirect = redirect()
                ->route('finance.tunggakan.index')
                ->with(
                    'success',
                    'Import selesai. Inserted: ' . $summary['inserted']
                    . ', Matched: ' . $summary['matched']
                    . ', Unmatched: ' . $summary['unmatched']
                    . ', Skipped: ' . $summary['skipped']
                    . '.'
                );

            if ((int) ($summary['not_found_in_student_db'] ?? 0) > 0) {
                $redirect->with(
                    'warning',
                    'Alert: '
                    . $summary['not_found_in_student_db']
                    . ' data siswa tidak ditemukan di database siswa.'
                );
            }

            return $redirect;
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('finance.tunggakan.index')
                ->with('error', 'Import Excel gagal: ' . $exception->getMessage());
        }
    }

    public function syncDatabase(Request $request)
    {
        $validated = $request->validate([
            'bulan_sync' => 'nullable|string|max:100',
        ]);

        $bulanSync = trim((string) ($validated['bulan_sync'] ?? ''));

        try {
            $summary = $this->tunggakanService->syncFromRecipientDatabase(
                bulan: $bulanSync,
                actorId: auth()->id() ? (string) auth()->id() : null
            );

            return redirect()
                ->route('finance.tunggakan.index')
                ->with(
                    'success',
                    'Sinkron DB recipient siswa selesai. Inserted: ' . $summary['inserted']
                    . ', Matched: ' . $summary['matched']
                    . ', Skipped (duplikat periode): ' . $summary['skipped']
                    . '.'
                );
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('finance.tunggakan.index')
                ->with('error', 'Sinkron DB recipient gagal: ' . $exception->getMessage());
        }
    }

    public function createDefaultTemplate()
    {
        try {
            $actorId = auth()->id() ? (string) auth()->id() : null;

            $waTemplate = BlastMessageTemplate::query()->updateOrCreate(
                [
                    'channel' => 'whatsapp',
                    'name' => 'Tunggakan WA Otomatis',
                ],
                [
                    'content' => "Yth. Bapak/Ibu {nama_wali},\n\nKami informasikan data tunggakan untuk {nama_siswa} ({kelas}):\n- Periode: {bulan_tunggakan}\n- Nilai periode ini: {nilai_tunggakan_rupiah}\n- Total tunggakan: {total_tunggakan_rupiah}\n- Tagihan saat ini: {tagihan_rupiah}\n\nMohon kesediaannya untuk melakukan pembayaran secepatnya.\nTerima kasih.",
                    'is_active' => true,
                    'created_by' => $actorId,
                ]
            );

            $emailTemplate = BlastMessageTemplate::query()->updateOrCreate(
                [
                    'channel' => 'email',
                    'name' => 'Tunggakan Email Otomatis',
                ],
                [
                    'content' => "Yth. Bapak/Ibu {nama_wali},\n\nBerikut informasi tunggakan untuk {nama_siswa} ({kelas}):\n- Periode tunggakan: {bulan_tunggakan}\n- Nilai periode ini: {nilai_tunggakan_rupiah}\n- Total tunggakan: {total_tunggakan_rupiah}\n- Tagihan saat ini: {tagihan_rupiah}\n\nMohon dapat segera dilakukan pembayaran.\n\nHormat kami,\nTim Keuangan",
                    'is_active' => true,
                    'created_by' => $actorId,
                ]
            );

            return redirect()
                ->route('finance.tunggakan.index')
                ->with(
                    'success',
                    'Template blasting tunggakan berhasil disiapkan: '
                    . $waTemplate->name . ' dan ' . $emailTemplate->name . '.'
                );
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('finance.tunggakan.index')
                ->with('error', 'Gagal menyiapkan template blasting tunggakan.');
        }
    }

    private function buildRecordsVersion(): string
    {
        $maxUpdatedAt = TunggakanRecord::query()->max('updated_at');
        if (empty($maxUpdatedAt)) {
            return '0';
        }

        $timestamp = strtotime((string) $maxUpdatedAt);
        return $timestamp !== false ? (string) $timestamp : md5((string) $maxUpdatedAt);
    }
}
