<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;

class AnnouncementApiController extends Controller
{
    public function index()
    {
        return response()->json(Announcement::latest()->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $announcement = Announcement::create([
            ...$validated,
            'created_by' => $request->user()->id,
        ]);

        return response()->json($announcement, 201);
    }
}
