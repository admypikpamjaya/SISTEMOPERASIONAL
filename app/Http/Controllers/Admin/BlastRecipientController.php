<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlastRecipient;
use App\Services\Recipient\ExcelImportService;
use App\Services\Recipient\RecipientBulkSaver;
use App\Services\Recipient\RecipientNormalizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BlastRecipientController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'q' => 'nullable|string|max:255',
            'kelas' => 'nullable|string|max:100',
            'per_page' => 'nullable|integer|min:1|max:500',
        ]);

        $search = trim((string) ($validated['q'] ?? ''));
        $selectedClass = trim((string) ($validated['kelas'] ?? ''));
        $allowedPerPage = [20, 50, 100, 200];
        $perPage = (int) ($validated['per_page'] ?? 50);

        if (!in_array($perPage, $allowedPerPage, true)) {
            $perPage = 50;
        }

        $query = BlastRecipient::query();

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('nama_siswa', 'like', '%' . $search . '%')
                    ->orWhere('kelas', 'like', '%' . $search . '%')
                    ->orWhere('nama_wali', 'like', '%' . $search . '%')
                    ->orWhere('wa_wali', 'like', '%' . $search . '%')
                    ->orWhere('wa_wali_2', 'like', '%' . $search . '%')
                    ->orWhere('email_wali', 'like', '%' . $search . '%');
            });
        }

        if ($selectedClass !== '') {
            $query->where('kelas', $selectedClass);
        }

        $recipients = $query
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        $kelasOptions = BlastRecipient::query()
            ->select('kelas')
            ->whereNotNull('kelas')
            ->where('kelas', '!=', '')
            ->distinct()
            ->orderBy('kelas')
            ->pluck('kelas');

        $baseStatsQuery = BlastRecipient::query();
        $totalRecipients = (clone $baseStatsQuery)->count();
        $completeCount = (clone $baseStatsQuery)
            ->whereNotNull('nama_siswa')
            ->whereNotNull('kelas')
            ->whereNotNull('nama_wali')
            ->where(function ($query) {
                $query->whereNotNull('wa_wali')
                    ->orWhereNotNull('wa_wali_2');
            })
            ->whereNotNull('email_wali')
            ->count();
        $incompleteCount = max(0, $totalRecipients - $completeCount);
        $validCount = (clone $baseStatsQuery)
            ->where('is_valid', true)
            ->count();

        return view('admin.blast.recipients.index', compact(
            'recipients',
            'kelasOptions',
            'search',
            'selectedClass',
            'allowedPerPage',
            'perPage',
            'totalRecipients',
            'completeCount',
            'incompleteCount',
            'validCount'
        ));
    }

    public function create()
    {
        return view('admin.blast.recipients.create');
    }

    /**
     * INPUT MANUAL (DENGAN NORMALIZATION)
     */
    public function store(
        Request $request,
        RecipientNormalizer $normalizer
    ) {
        $data = $request->validate([
            'nama_siswa' => 'required|string',
            'kelas' => 'required|string',
            'nama_wali' => 'required|string',
            'email_wali' => 'nullable|email',
            'wa_wali' => 'nullable|string',
            'wa_wali_2' => 'nullable|string',
            'catatan' => 'nullable|string',
        ]);

        if (empty($data['email_wali']) && empty($data['wa_wali']) && empty($data['wa_wali_2'])) {
            return back()->withErrors([
                'email_wali' => 'Email atau WhatsApp wajib diisi'
            ])->withInput();
        }

        $dto = $normalizer->normalize([
            'nama_siswa' => $data['nama_siswa'],
            'kelas' => $data['kelas'],
            'nama_wali' => $data['nama_wali'],
            'email' => $data['email_wali'],
            'wa' => $data['wa_wali'],
            'wa_2' => $data['wa_wali_2'] ?? null,
            'catatan' => $data['catatan'] ?? null,
        ]);

        BlastRecipient::create([
            'nama_siswa' => $dto->namaSiswa,
            'kelas' => $dto->kelas,
            'nama_wali' => $dto->namaWali,
            'email_wali' => $dto->email,
            'wa_wali' => $dto->phone,
            'wa_wali_2' => $dto->phoneSecondary,
            'catatan' => $dto->catatan,
            'is_valid' => empty($dto->errors),
            'validation_error' => empty($dto->errors)
                ? null
                : implode(', ', $dto->errors),
        ]);

        return redirect()
            ->route('admin.blast.recipients.index')
            ->with('success', 'Penerima berhasil ditambahkan');
    }

    /**
     * FORM EDIT
     */
    public function edit(string $id)
    {
        $recipient = BlastRecipient::findOrFail($id);

        return view('admin.blast.recipients.edit', compact('recipient'));
    }

    /**
     * UPDATE DATA (DENGAN NORMALIZATION)
     */
    public function update(
        Request $request,
        string $id,
        RecipientNormalizer $normalizer
    ) {
        $recipient = BlastRecipient::findOrFail($id);

        $data = $request->validate([
            'nama_siswa' => 'required|string',
            'kelas' => 'required|string',
            'nama_wali' => 'required|string',
            'email_wali' => 'nullable|email',
            'wa_wali' => 'nullable|string',
            'wa_wali_2' => 'nullable|string',
            'catatan' => 'nullable|string',
        ]);

        if (empty($data['email_wali']) && empty($data['wa_wali']) && empty($data['wa_wali_2'])) {
            return back()->withErrors([
                'email_wali' => 'Email atau WhatsApp wajib diisi'
            ])->withInput();
        }

        $dto = $normalizer->normalize([
            'nama_siswa' => $data['nama_siswa'],
            'kelas' => $data['kelas'],
            'nama_wali' => $data['nama_wali'],
            'email' => $data['email_wali'],
            'wa' => $data['wa_wali'],
            'wa_2' => $data['wa_wali_2'] ?? null,
            'catatan' => $data['catatan'] ?? null,
        ]);

        $recipient->update([
            'nama_siswa' => $dto->namaSiswa,
            'kelas' => $dto->kelas,
            'nama_wali' => $dto->namaWali,
            'email_wali' => $dto->email,
            'wa_wali' => $dto->phone,
            'wa_wali_2' => $dto->phoneSecondary,
            'catatan' => $dto->catatan,
            'is_valid' => empty($dto->errors),
            'validation_error' => empty($dto->errors)
                ? null
                : implode(', ', $dto->errors),
        ]);

        return redirect()
            ->route('admin.blast.recipients.index')
            ->with('success', 'Data penerima berhasil diperbarui');
    }

    /**
     * IMPORT EXCEL
     */
    public function import(
        Request $request,
        ExcelImportService $importService,
        RecipientBulkSaver $bulkSaver
    ) {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv,xls'
        ]);

        $uploadedFile = $request->file('file');

        if ($uploadedFile === null) {
            return redirect()
                ->route('admin.blast.recipients.index')
                ->with('error', 'Import gagal: file tidak ditemukan.');
        }

        try {
            $result = $importService->import(
                $uploadedFile->getPathname()
            );
        } catch (\Throwable $e) {
            Log::error('[RECIPIENT IMPORT FAILED]', [
                'file' => $uploadedFile->getClientOriginalName(),
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('admin.blast.recipients.index')
                ->with('error', 'Import gagal: ' . $e->getMessage());
        }

        if (empty($result->valid) && empty($result->invalid)) {
            return redirect()
                ->route('admin.blast.recipients.index')
                ->with('error', 'Import gagal: file tidak berisi data yang dapat diproses.');
        }

        $summary = $bulkSaver->save(collect($result->valid));
        $invalidCount = count($result->invalid) + (int) ($summary['invalid'] ?? 0);
        $message = "Import selesai. Inserted: {$summary['inserted']}, Duplicate: {$summary['duplicates']}, Invalid: {$invalidCount}";

        if ((int) $summary['inserted'] === 0) {
            return redirect()
                ->route('admin.blast.recipients.index')
                ->with('error', $message . ' Tidak ada data baru yang disimpan.');
        }

        return redirect()
            ->route('admin.blast.recipients.index')
            ->with('success', $message);
    }

    public function destroy(string $id)
    {
        BlastRecipient::findOrFail($id)->delete();

        return back()->with('success', 'Penerima dihapus');
    }
}
