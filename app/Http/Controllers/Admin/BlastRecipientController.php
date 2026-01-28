<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlastRecipient;
use App\Services\Recipient\ExcelImportService;
use App\Services\Recipient\RecipientBulkSaver;
use Illuminate\Http\Request;

class BlastRecipientController extends Controller
{
    public function index()
    {
        $recipients = BlastRecipient::latest()->paginate(20);

        // FIX: view path sesuai folder
        return view('admin.blast.recipients.index', compact('recipients'));
    }

    public function create()
    {
        return view('admin.blast.recipients.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_siswa' => 'required|string',
            'kelas' => 'required|string',
            'nama_wali' => 'required|string',
            'email_wali' => 'nullable|email',
            'wa_wali' => 'nullable|string',
            'catatan' => 'nullable|string',
        ]);

        // RULE FINAL: email / wa minimal salah satu
        if (empty($data['email_wali']) && empty($data['wa_wali'])) {
            return back()->withErrors([
                'email_wali' => 'Email atau WhatsApp wajib diisi'
            ])->withInput();
        }

        BlastRecipient::create([
            ...$data,
            'is_valid' => true,
            'validation_error' => null,
        ]);

        return redirect()
            ->route('admin.blast_recipients.index')
            ->with('success', 'Penerima berhasil ditambahkan');
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

        // SIMPAN YANG VALID
        $summary = $bulkSaver->save(collect($result->valid));

        return redirect()
            ->route('admin.blast_recipients.index')
            ->with('success', "Import selesai. Inserted: {$summary['inserted']}, Duplicate: {$summary['duplicates']}, Invalid: {$summary['invalid']}");
    }

    public function destroy(string $id)
    {
        BlastRecipient::findOrFail($id)->delete();

        return back()->with('success', 'Penerima dihapus');
    }
}
