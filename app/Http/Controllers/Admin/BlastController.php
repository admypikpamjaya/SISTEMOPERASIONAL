<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BlastController extends Controller
{
    /**
     * Display blast page.
     */
    public function index()
    {
        return view('admin.blast.index');
    }

    /**
     * Send blast (dummy).
     */
    public function send(Request $request)
    {
        $request->validate([
            'content' => 'required|string',
        ]);

        // PHASE 6.2:
        // Belum kirim ke mana-mana

        return redirect()
            ->back()
            ->with('success', 'Blast berhasil dikirim (dummy)');
    }
}
