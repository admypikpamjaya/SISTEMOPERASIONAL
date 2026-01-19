<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BlastApiController extends Controller
{
    public function send(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string',
        ]);

        return response()->json([
            'message' => 'Blast sent',
            'payload' => $validated['message']
        ]);
    }
}
