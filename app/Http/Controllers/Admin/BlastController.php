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
     |  VIEW
     ======================= */

    public function index()
    {
        return view('admin.blast.index');
    }

    public function whatsapp()
    {
        $recipients = BlastRecipient::whereNotNull('wa_wali')
            ->where('is_valid', true)
            ->orderBy('nama_siswa')
            ->get();

        $templates = BlastMessageTemplate::where('channel', 'whatsapp')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.blast.whatsapp', compact(
            'recipients',
            'templates'
        ));
    }

    public function email()
    {
        return view('admin.blast.email');
    }

    /* =======================
     |  WHATSAPP BLAST
     ======================= */

    public function sendWhatsapp(
        Request $request,
        TemplateRenderer $renderer
    ) {
        $validated = $request->validate([
            'recipient_ids' => 'nullable|array',
            'recipient_ids.*' => 'string',
            'template_id' => 'nullable|string',

            'targets' => 'nullable|string',
            'message' => 'nullable|string',

            'messages' => 'nullable|array',
            'messages.*' => 'nullable|string',

            'attachments.*' => 'nullable|file|max:5120',
        ]);

        /*
        |--------------------------------------------------------------------------
        | MODE 1 - DATABASE RECIPIENT
        |--------------------------------------------------------------------------
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

                // PRIORITY:
                // 1. Per-recipient message
                // 2. Template
                // 3. Global message

                $customMessage = $validated['messages'][$recipient->id] ?? null;

                if (!empty($customMessage)) {
                    $message = $customMessage;
                } elseif ($template) {
                    $message = $renderer->render(
                        $template->content,
                        $recipient
                    );
                } else {
                    $message = $validated['message'] ?? '';
                }

                $payload = new BlastPayload($message);
                $payload->setMeta('channel', 'WHATSAPP');
                $payload->setMeta('sent_by', Auth::id());
                $payload->setMeta('recipient_id', $recipient->id);

                // Attachment (same file for all recipients)
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
                'WhatsApp blast berhasil diproses.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | MODE 2 - MANUAL TARGET (LEGACY)
        |--------------------------------------------------------------------------
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

    /* =======================
     |  EMAIL (TIDAK DIUBAH)
     ======================= */

    public function sendEmail(
        Request $request,
        TemplateRenderer $renderer
    ) {
        $validated = $request->validate([
            'recipient_ids' => 'nullable|array',
            'recipient_ids.*' => 'string',
            'template_id' => 'nullable|string',

            'targets' => 'nullable|string',
            'subject' => 'required|string',
            'message' => 'nullable|string',

            'attachments' => 'nullable|array',
            'attachments.*' => 'nullable|file|max:5120',
        ]);

        $attachments = $this->storeEmailAttachments($request);

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
                $this->attachFilesToPayload($payload, $attachments);

                dispatch(
                    new SendEmailBlastJob(
                        $recipient->email_wali,
                        $validated['subject'],
                        $payload
                    )
                );
            }

            return back()->with('success', 'Email blast queued.');
        }

        $targets = array_filter(
            array_map('trim', explode(',', $validated['targets'] ?? ''))
        );

        foreach ($targets as $email) {
            $payload = new BlastPayload($validated['message'] ?? '');
            $payload->setMeta('channel', 'EMAIL');
            $payload->setMeta('sent_by', Auth::id());
            $this->attachFilesToPayload($payload, $attachments);

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

    /* =======================
     |  RECIPIENT SELECTOR API
     ======================= */

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

    /**
     * @return BlastAttachment[]
     */
    private function storeEmailAttachments(Request $request): array
    {
        if (!$request->hasFile('attachments')) {
            return [];
        }

        $attachments = [];
        $folder = 'blasts/' . Str::uuid();

        foreach ($request->file('attachments') as $file) {
            $path = $file->store($folder, 'public');

            $attachments[] = new BlastAttachment(
                storage_path('app/public/' . $path),
                $file->getClientOriginalName(),
                $file->getMimeType() ?: 'application/octet-stream'
            );
        }

        return $attachments;
    }

    /**
     * @param BlastAttachment[] $attachments
     */
    private function attachFilesToPayload(
        BlastPayload $payload,
        array $attachments
    ): void {
        foreach ($attachments as $attachment) {
            $payload->addAttachment($attachment);
        }
    }
}
