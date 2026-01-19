<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReminderController extends Controller
{
    /**
     * Display reminder page.
     */
    public function index()
    {
        return view('admin.reminders.index');
    }

    /**
     * Send reminder (dummy).
     */
    public function send(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        // PHASE 6.2:
        // Belum WA / Email
        // Hanya bukti request masuk

        return redirect()
            ->back()
            ->with('success', 'Reminder berhasil dikirim (dummy)');
    }
}
