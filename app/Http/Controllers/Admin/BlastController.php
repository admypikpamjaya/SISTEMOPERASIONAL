<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\DataTransferObjects\BlastPayload;
use App\DataTransferObjects\BlastAttachment;
use App\Jobs\Blast\SendWhatsappBlastJob;
use App\Jobs\Blast\SendEmailBlastJob;
use App\Services\Blast\RecipientSelectorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class BlastController extends Controller
{
    /* =======================
     |  VIEW (JANGAN DIUBAH)
     ======================= */

    public function index()
    {
        return view('admin.blast.index');
    }

    public function whatsapp()
    {
        return view('admin.blast.whatsapp');
    }

    public function email()
    {
        return view('admin.blast.email');
    }

    /* =======================
     |  ACTION
     ======================= */

    public function sendWhatsapp(Request $request)
    {
        $validated = $request->validate([
            'targets' => 'required|string',
            'message' => 'required|string',
            'attachments.*' => 'nullable|file|max:5120',
        ]);

        $payload = new BlastPayload($validated['message']);
        $payload->setMeta('channel', 'WHATSAPP');
        $payload->setMeta('sent_by', Auth::id());

        // Attachment (Phase 8.6 – optional)
        if ($request->hasFile('attachments')) {
            $folder = 'blasts/' . Str::uuid();

            foreach ($request->file('attachments') as $file) {
                $path = $file->store($folder, 'public');

                $payload->addAttachment(
                    new BlastAttachment(
                        public_path('storage/' . $path),
                        $file->getClientOriginalName(),
                        $file->getMimeType()
                    )
                );
            }
        }

        $targets = array_filter(
            array_map('trim', explode(',', $validated['targets']))
        );

        foreach ($targets as $target) {
            dispatch(
                new SendWhatsappBlastJob($target, $payload)
            );
        }

        return back()->with('success', 'WhatsApp blast queued.');
    }

    public function sendEmail(Request $request)
    {
        $validated = $request->validate([
            'targets' => 'required|string',
            'subject' => 'required|string',
            'message' => 'required|string',
            'attachments.*' => 'nullable|file|max:5120',
        ]);

        $payload = new BlastPayload($validated['message']);
        $payload->setMeta('channel', 'EMAIL');
        $payload->setMeta('sent_by', Auth::id());

        // Attachment
        if ($request->hasFile('attachments')) {
            $folder = 'blasts/' . Str::uuid();

            foreach ($request->file('attachments') as $file) {
                $path = $file->store($folder, 'public');

                $payload->addAttachment(
                    new BlastAttachment(
                        public_path('storage/' . $path),
                        $file->getClientOriginalName(),
                        $file->getMimeType()
                    )
                );
            }
        }

        $targets = array_filter(
            array_map('trim', explode(',', $validated['targets']))
        );

        foreach ($targets as $email) {
            dispatch(
                new SendEmailBlastJob(
                    $email,
                    $validated['subject'],
                    $payload
                )
            );
        }

        return back()->with('success', 'Email blast queued.');
    }
    public function recipients(
    Request $request,
    RecipientSelectorService $service
) {
    $validated = $request->validate([
        'channel' => 'required|in:email,whatsapp',
        'ids' => 'nullable|array',
        'ids.*' => 'string',
    ]);

    // MULTIPLE SELECTOR
    if (!empty($validated['ids'])) {
        return response()->json(
            $service->getByIds($validated['ids'])
        );
    }

    // DEFAULT SELECTOR (BY CHANNEL)
    return response()->json(
        $service->getSelectable($validated['channel'])
    );
}
}
