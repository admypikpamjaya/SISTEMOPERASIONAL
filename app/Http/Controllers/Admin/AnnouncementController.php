<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    /**
     * Display announcement page (UI).
     */
    public function index()
    {
        return view('admin.announcements.index');
    }

    /**
     * Store announcement (dummy – phase 6.2).
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'   => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        // PHASE 6.2:
        // Belum simpan DB, belum kirim apapun
        // Cuma validasi + bukti alur jalan

        return redirect()
            ->back()
            ->with('success', 'Announcement berhasil diterima (dummy)');
    }
}
