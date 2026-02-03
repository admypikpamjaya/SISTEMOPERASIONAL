<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlastTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BlastTemplateController extends Controller
{
    public function index()
    {
        $templates = BlastTemplate::latest()->get();

        return view('admin.blast.templates.index', compact('templates'));
    }

    public function create()
    {
        return view('admin.blast.templates.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'channel' => 'required|in:EMAIL,WHATSAPP',
            'subject' => 'nullable|string',
            'body' => 'required|string',
        ]);

        BlastTemplate::create([
            ...$data,
            'created_by' => Auth::id(),
        ]);

        return redirect()
            ->route('admin.blast.templates.index')
            ->with('success', 'Template berhasil dibuat');
    }

    public function destroy(string $id)
    {
        BlastTemplate::findOrFail($id)->delete();

        return back()->with('success', 'Template dihapus');
    }
}
