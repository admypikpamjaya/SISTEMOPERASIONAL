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
        return view('admin.recipient.index', compact('recipients'));
    }

    public function create()
    {
        return view('admin.recipient.create');
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

        BlastRecipient::create([
            ...$data,
            'is_valid' => true,
            'validation_error' => null,
        ]);

        return redirect()
            ->route('admin.blast.recipients.index')
            ->with('success', 'Penerima berhasil ditambahkan');
    }

    public function edit(string $id)
    {
        $recipient = BlastRecipient::findOrFail($id);
        return view('admin.recipient.create', compact('recipient'));
    }

    public function update(Request $request, string $id)
    {
        $recipient = BlastRecipient::findOrFail($id);

        $data = $request->validate([
            'nama_siswa' => 'required|string',
            'kelas' => 'required|string',
            'nama_wali' => 'required|string',
            'email_wali' => 'nullable|email',
            'wa_wali' => 'nullable|string',
            'catatan' => 'nullable|string',
        ]);

        $recipient->update([
            ...$data,
            'is_valid' => true,
            'validation_error' => null,
        ]);

        return redirect()
            ->route('admin.blast.recipients.index')
            ->with('success', 'Penerima berhasil diperbarui');
    }

    public function destroy(string $id)
    {
        BlastRecipient::findOrFail($id)->delete();

        return back()->with('success', 'Penerima dihapus');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv',
        ]);

        $importer = app(ExcelImportService::class);
        $saver = app(RecipientBulkSaver::class);

        $result = $importer->import($request->file('file')->getRealPath());
        $summary = $saver->save(collect([...$result->valid, ...$result->invalid]));

        return back()->with('success', "Import selesai. Inserted: {$summary['inserted']}");
    }
}
