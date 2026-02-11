<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\DataTransferObjects\BlastPayload;
use App\DataTransferObjects\BlastAttachment;
use App\Jobs\Blast\SendWhatsappBlastJob;
use App\Jobs\Blast\SendEmailBlastJob;
use App\Models\BlastRecipient;
use App\Models\BlastMessageTemplate;
use App\Services\Blast\TemplateRenderer;
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

    /**
     * =======================
     * WHATSAPP BLAST
     * =======================
     */
    public function sendWhatsapp(
        Request $request,
        TemplateRenderer $renderer
    ) {
        $validated = $request->validate([
            // MODE DB
            'recipient_ids' => 'nullable|array',
            'recipient_ids.*' => 'string',
            'template_id' => 'nullable|string',

            // MODE MANUAL (fallback)
            'targets' => 'nullable|string',
            'message' => 'nullable|string',

            'attachments.*' => 'nullable|file|max:5120',
        ]);

        /**
         * =======================
         * MODE 1: DATABASE RECIPIENT
         * =======================
         */
        if (!empty($validated['recipient_ids'])) {

            $recipients = BlastRecipient::whereIn(
                'id',
                $validated['recipient_ids']
            )->whereNotNull('wa_wali')->get();

            $template = null;
            if (!empty($validated['template_id'])) {
                $template = BlastMessageTemplate::find(
                    $validated['template_id']
                );
            }

            foreach ($recipients as $recipient) {

                // Render message
                $message = $template
                    ? $renderer->render($template->content, $recipient)
                    : ($validated['message'] ?? '');

                $payload = new BlastPayload($message);
                $payload->setMeta('channel', 'WHATSAPP');
                $payload->setMeta('sent_by', Auth::id());
                $payload->setMeta('recipient_id', $recipient->id);

                // Attachment (optional)
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

                dispatch(
                    new SendWhatsappBlastJob(
                        $recipient->wa_wali,
                        $payload
                    )
                );
            }

            return back()->with(
                'success',
                'WhatsApp blast (DB recipients) queued.'
            );
        }

        /**
         * =======================
         * MODE 2: MANUAL (LEGACY)
         * =======================
         */
        $targets = array_filter(
            array_map('trim', explode(',', $validated['targets'] ?? ''))
        );

        foreach ($targets as $target) {
            $payload = new BlastPayload($validated['message'] ?? '');
            $payload->setMeta('channel', 'WHATSAPP');
            $payload->setMeta('sent_by', Auth::id());

            dispatch(
                new SendWhatsappBlastJob($target, $payload)
            );
        }

        return back()->with('success', 'WhatsApp blast queued.');
    }

    /**
     * =======================
     * EMAIL BLAST
     * =======================
     */
    public function sendEmail(
        Request $request,
        TemplateRenderer $renderer
    ) {
        $validated = $request->validate([
            // MODE DB
            'recipient_ids' => 'nullable|array',
            'recipient_ids.*' => 'string',
            'template_id' => 'nullable|string',

            // MODE MANUAL
            'targets' => 'nullable|string',
            'subject' => 'required|string',
            'message' => 'nullable|string',

            'attachments.*' => 'nullable|file|max:5120',
        ]);

        /**
         * MODE DB
         */
        if (!empty($validated['recipient_ids'])) {

            $recipients = BlastRecipient::whereIn(
                'id',
                $validated['recipient_ids']
            )->whereNotNull('email_wali')->get();

            $template = null;
            if (!empty($validated['template_id'])) {
                $template = BlastMessageTemplate::find(
                    $validated['template_id']
                );
            }

            foreach ($recipients as $recipient) {

                $message = $template
                    ? $renderer->render($template->content, $recipient)
                    : ($validated['message'] ?? '');

                $payload = new BlastPayload($message);
                $payload->setMeta('channel', 'EMAIL');
                $payload->setMeta('sent_by', Auth::id());
                $payload->setMeta('recipient_id', $recipient->id);

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

                dispatch(
                    new SendEmailBlastJob(
                        $recipient->email_wali,
                        $validated['subject'],
                        $payload
                    )
                );
            }

            return back()->with(
                'success',
                'Email blast (DB recipients) queued.'
            );
        }

        /**
         * MODE MANUAL
         */
        $targets = array_filter(
            array_map('trim', explode(',', $validated['targets'] ?? ''))
        );

        foreach ($targets as $email) {
            $payload = new BlastPayload($validated['message'] ?? '');
            $payload->setMeta('channel', 'EMAIL');
            $payload->setMeta('sent_by', Auth::id());

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

    /**
     * =======================
     * RECIPIENT SELECTOR API
     * =======================
     */
    public function recipients(
        Request $request,
        RecipientSelectorService $service
    ) {
        $validated = $request->validate([
            'channel' => 'required|in:email,whatsapp',
            'ids' => 'nullable|array',
            'ids.*' => 'string',
        ]);

        if (!empty($validated['ids'])) {
            return response()->json(
                $service->getByIds($validated['ids'])
            );
        }

        return response()->json(
            $service->getSelectable($validated['channel'])
        );
    }
}