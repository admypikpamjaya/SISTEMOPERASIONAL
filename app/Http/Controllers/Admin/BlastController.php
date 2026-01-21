<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\DataTransferObjects\BlastPayload;
use App\DataTransferObjects\BlastAttachment;
use App\Jobs\Blast\SendWhatsappBlastJob;
use App\Jobs\Blast\SendEmailBlastJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class BlastController extends Controller
{
    public function index()
    {
        return view('admin.blast.index');
    }

    public function send(Request $request)
    {
        $request->validate([
            'channel'      => 'required|in:WHATSAPP,EMAIL',
            'targets'      => 'required|string',
            'message'      => 'required|string',
            'attachments.*'=> 'file|max:10240', // max 10MB
        ]);

        // ===============================
        // Build Payload
        // ===============================
        $payload = new BlastPayload($request->message);
        $payload->setMeta('source', 'manual_blast');
        $payload->setMeta('created_by', Auth::id());

        // ===============================
        // Handle Attachments
        // ===============================
        if ($request->hasFile('attachments')) {
            $uuid = (string) Str::uuid();
            $basePath = "blasts/{$uuid}";

            foreach ($request->file('attachments') as $file) {
                $storedPath = $file->store($basePath);

                $payload->addAttachment(
                    new BlastAttachment(
                        storage_path("app/{$storedPath}"),
                        $file->getClientOriginalName(),
                        $file->getMimeType()
                    )
                );
            }
        }

        // ===============================
        // Dispatch Jobs
        // ===============================
        $targets = array_map('trim', explode(',', $request->targets));

        foreach ($targets as $target) {
            if ($request->channel === 'WHATSAPP') {
                dispatch(new SendWhatsappBlastJob($target, $payload));
            } else {
                dispatch(new SendEmailBlastJob($target, $payload));
            }
        }

        return redirect()
            ->back()
            ->with('success', 'Blast berhasil dikirim ke queue.');
    }
}
