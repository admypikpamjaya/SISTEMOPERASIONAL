<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\DataTransferObjects\BlastPayload;
use App\DataTransferObjects\BlastAttachment;
use App\Jobs\Blast\SendWhatsappBlastJob;
use App\Jobs\Blast\SendEmailBlastJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BlastController extends Controller
{
    /* =======================
     |  VIEW
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
        ]);

        $payload = new BlastPayload($validated['message']);
        $payload->setMeta('channel', 'WHATSAPP');
        $payload->setMeta('sent_by', Auth::id());

        $targets = array_filter(array_map('trim', explode(',', $validated['targets'])));

        foreach ($targets as $target) {
            dispatch(new SendWhatsappBlastJob($target, $payload));
        }

        return back()->with('success', 'WhatsApp blast queued.');
    }

    public function sendEmail(Request $request)
    {
        $validated = $request->validate([
            'targets' => 'required|string',
            'subject' => 'required|string',
            'message' => 'required|string',
        ]);

        $payload = new BlastPayload($validated['message']);
        $payload->setMeta('channel', 'EMAIL');
        $payload->setMeta('subject', $validated['subject']);
        $payload->setMeta('sent_by', Auth::id());

        $targets = array_filter(array_map('trim', explode(',', $validated['targets'])));

        foreach ($targets as $target) {
            dispatch(new SendEmailBlastJob($target, $payload));
        }

        return back()->with('success', 'Email blast queued.');
    }
}
