<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlastRecipient;
use App\Services\Recipient\ExcelImportService;
use App\Services\Recipient\RecipientBulkSaver;
use App\Services\Recipient\RecipientNormalizer;
use Illuminate\Http\Request;

class BlastRecipientController extends Controller
{
    public function index()
    {
        $recipients = BlastRecipient::latest()->paginate(20);

        return view('admin.blast.recipients.index', compact('recipients'));
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
            'catatan' => 'nullable|string',
        ]);

        if (empty($data['email_wali']) && empty($data['wa_wali'])) {
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
            'catatan' => $data['catatan'] ?? null,
        ]);

        BlastRecipient::create([
            'nama_siswa' => $dto->namaSiswa,
            'kelas' => $dto->kelas,
            'nama_wali' => $dto->namaWali,
            'email_wali' => $dto->email,
            'wa_wali' => $dto->phone,
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
            'catatan' => 'nullable|string',
        ]);

        if (empty($data['email_wali']) && empty($data['wa_wali'])) {
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
            'catatan' => $data['catatan'] ?? null,
        ]);

        $recipient->update([
            'nama_siswa' => $dto->namaSiswa,
            'kelas' => $dto->kelas,
            'nama_wali' => $dto->namaWali,
            'email_wali' => $dto->email,
            'wa_wali' => $dto->phone,
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

        $result = $importService->import(
            $request->file('file')->getRealPath()
        );

        $summary = $bulkSaver->save(collect($result->valid));

        return redirect()
            ->route('admin.blast.recipients.index')
            ->with(
                'success',
                "Import selesai. Inserted: {$summary['inserted']}, Duplicate: {$summary['duplicates']}, Invalid: {$summary['invalid']}"
            );
    }

    public function destroy(string $id)
    {
        BlastRecipient::findOrFail($id)->delete();

        return back()->with('success', 'Penerima dihapus');
    }
}
