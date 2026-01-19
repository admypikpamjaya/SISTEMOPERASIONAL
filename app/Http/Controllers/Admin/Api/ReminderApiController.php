<?php
namespace App\Http\Controllers\Admin\Api;
use App\Http\Controllers\Controller;
use App\Models\Reminder;
use Illuminate\Http\Request;
class ReminderApiController extends Controller
{
    public function index()
    {
        return response()->json(Reminder::latest()->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'remind_at' => 'required|date',
        ]);

        $reminder = Reminder::create($validated);

        return response()->json($reminder, 201);
    }
}