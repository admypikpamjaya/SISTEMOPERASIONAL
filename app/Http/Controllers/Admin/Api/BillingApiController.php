<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Billing;
use Illuminate\Http\Request;

class BillingApiController extends Controller
{
    public function index()
    {
        return response()->json(Billing::latest()->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $billing = Billing::create($validated);

        return response()->json($billing, 201);
    }

    public function markPaid($id)
    {
        $billing = Billing::findOrFail($id);
        $billing->update(['paid_at' => now()]);

        return response()->json(['message' => 'Billing marked as paid']);
    }
}
