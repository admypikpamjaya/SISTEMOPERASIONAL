<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlastMessageTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BlastMessageTemplateController extends Controller
{
    public function index()
    {
        $templates = BlastMessageTemplate::latest()->paginate(20);

        return view('admin.blast.templates.index', compact('templates'));
    }

    public function create()
    {
        return view('admin.blast.templates.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'channel' => 'required|in:EMAIL,WHATSAPP',
            'name' => 'required|string',
            'subject' => 'nullable|string',
            'body' => 'required|string',
            'is_active' => 'boolean',
        ]);

        BlastMessageTemplate::create([
            ...$data,
            'created_by' => Auth::id(),
            'is_active' => $data['is_active'] ?? true,
        ]);

        return redirect()
            ->route('admin.blast.templates.index')
            ->with('success', 'Template berhasil dibuat');
    }

    public function edit(string $id)
    {
        $template = BlastMessageTemplate::findOrFail($id);

        return view('admin.blast.templates.edit', compact('template'));
    }

    public function update(Request $request, string $id)
    {
        $template = BlastMessageTemplate::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string',
            'subject' => 'nullable|string',
            'body' => 'required|string',
            'is_active' => 'boolean',
        ]);

        $template->update($data);

        return redirect()
            ->route('admin.blast.templates.index')
            ->with('success', 'Template diperbarui');
    }

    public function destroy(string $id)
    {
        BlastMessageTemplate::findOrFail($id)->delete();

        return back()->with('success', 'Template dihapus');
    }
}
