<?php

namespace App\Http\Controllers;

use App\Models\AnnouncementLog;
use Illuminate\Http\Response;

class AnnouncementTrackingController extends Controller
{
    public function open(string $token): Response
    {
        $log = AnnouncementLog::query()
            ->where('track_token', $token)
            ->first();

        if ($log) {
            $log->update([
                'opened_at' => $log->opened_at ?? now(),
                'open_count' => (int) $log->open_count + 1,
            ]);
        }

        return response(base64_decode('R0lGODlhAQABAPAAAAAAAAAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw=='), 200, [
            'Content-Type' => 'image/gif',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => 'Mon, 01 Jan 1990 00:00:00 GMT',
        ]);
    }
}
