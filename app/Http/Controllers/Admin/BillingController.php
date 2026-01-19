<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class BillingController extends Controller
{
    /**
     * Display billing page.
     */
    public function index()
    {
        return view('admin.billings.index');
    }

    /**
     * Confirm billing payment (dummy).
     */
    public function confirmPayment($billingId)
    {
        // PHASE 6.2:
        // Belum update DB, belum transaksi
        // Hanya simulasi flow

        return redirect()
            ->back()
            ->with('success', "Billing ID {$billingId} berhasil dikonfirmasi (dummy)");
    }
}
