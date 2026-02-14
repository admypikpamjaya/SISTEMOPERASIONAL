<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\DataTransferObjects\BlastPayload;
use App\DataTransferObjects\BlastAttachment;
use App\Jobs\Blast\SendWhatsappBlastJob;
use App\Jobs\Blast\SendEmailBlastJob;
use App\Models\BlastRecipient;
use App\Models\BlastMessageTemplate;
use App\Models\BlastMessage;
use App\Models\BlastTarget;
use App\Models\BlastLog;
use App\Services\Blast\TemplateRenderer;
use App\Services\Blast\RecipientSelectorService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
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
        $recipients = $this->getRecipientsByChannel('whatsapp');

        $templates = BlastMessageTemplate::where('channel', 'whatsapp')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $activityData = $this->buildChannelActivityData('WHATSAPP', $recipients);
        $activityLogs = $activityData['logs'];
        $activityStats = $activityData['stats'];

        return view('admin.blast.whatsapp', compact(
            'recipients',
            'templates',
            'activityLogs',
            'activityStats'
        ));
    }

    public function email()
    {
        $recipients = $this->getRecipientsByChannel('email');

        $templates = BlastMessageTemplate::query()
            ->whereIn('channel', ['EMAIL', 'email'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $activityData = $this->buildChannelActivityData('EMAIL', $recipients);
        $activityLogs = $activityData['logs'];
        $activityStats = $activityData['stats'];

        return view(
            'admin.blast.email',
            compact('recipients', 'templates', 'activityLogs', 'activityStats')
        );
    }

    public function activity(Request $request)
    {
        $validated = $request->validate([
            'channel' => 'required|in:email,whatsapp',
        ]);

        $channel = strtolower((string) $validated['channel']);
        $recipients = $this->getRecipientsByChannel($channel);
        $activityData = $this->buildChannelActivityData(
            strtoupper($channel),
            $recipients
        );

        return response()->json($activityData);
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
            'use_global_default' => 'nullable|boolean',
            'message_overrides' => 'nullable|string',
            'attachment_override_keys' => 'nullable|array',
            'attachment_override_keys.*' => 'nullable|string',
            'attachment_overrides' => 'nullable|array',
            'attachment_overrides.*' => 'nullable|array',
            'attachment_overrides.*.*' => 'nullable|file|max:5120',

            'messages' => 'nullable|array',
            'messages.*' => 'nullable|string',

            'attachments' => 'nullable|array',
            'attachments.*' => 'nullable|file|max:5120',
        ]);

        $attachments = $this->storeEmailAttachments($request);
        $recipientAttachmentOverrides = $this->storeRecipientAttachmentOverrides(
            $request,
            $validated['attachment_override_keys'] ?? []
        );
        $useGlobalDefault = $request->boolean('use_global_default');
        $messageOverrides = $this->parseMessageOverrides(
            $validated['message_overrides'] ?? null
        );

        // Backward compatibility: old UI used messages[recipient_id].
        if (empty($messageOverrides) && !empty($validated['messages'])) {
            foreach ($validated['messages'] as $recipientId => $legacyMessage) {
                $legacyMessage = trim((string) $legacyMessage);
                if ($legacyMessage === '') {
                    continue;
                }

                $messageOverrides['db:' . (string) $recipientId] = [
                    'mode' => 'manual',
                    'message' => $legacyMessage,
                ];
            }
        }

        $template = null;
        if (!empty($validated['template_id'])) {
            $template = BlastMessageTemplate::query()
                ->where('id', $validated['template_id'])
                ->whereIn('channel', ['WHATSAPP', 'whatsapp'])
                ->where('is_active', true)
                ->first();
        }
        $blastMessage = null;

        if (!empty($validated['recipient_ids'])) {
            $recipients = BlastRecipient::whereIn(
                'id',
                $validated['recipient_ids']
            )
                ->whereNotNull('wa_wali')
                ->where('is_valid', true)
                ->get();

            foreach ($recipients as $recipient) {
                $target = $this->normalizeWhatsappTarget($recipient->wa_wali);
                if ($target === null) {
                    continue;
                }

                $message = $this->resolveDbRecipientWhatsappMessage(
                    recipient: $recipient,
                    renderer: $renderer,
                    template: $template,
                    globalMessage: $validated['message'] ?? '',
                    useGlobalDefault: $useGlobalDefault,
                    messageOverrides: $messageOverrides
                );

                if ($blastMessage === null) {
                    $blastMessage = $this->createBlastMessageRecord(
                        channel: 'WHATSAPP',
                        subject: null,
                        fallbackMessage: $validated['message'] ?? '',
                        template: $template
                    );
                }

                $blastLog = $this->createBlastLogRecord(
                    blastMessage: $blastMessage,
                    target: $target,
                    messageSnapshot: $message
                );

                $payload = new BlastPayload($message);
                $payload->setMeta('channel', 'WHATSAPP');
                $payload->setMeta('sent_by', Auth::id());
                $payload->setMeta('recipient_id', $recipient->id);
                $payload->setMeta('blast_log_id', $blastLog->id);
                $this->attachFilesToPayload($payload, $attachments);
                $this->attachFilesToPayload(
                    $payload,
                    $recipientAttachmentOverrides['db:' . $recipient->id] ?? []
                );

                dispatch(new SendWhatsappBlastJob($target, $payload));
            }
        }

        $rawTargets = array_filter(
            array_map('trim', explode(',', $validated['targets'] ?? ''))
        );
        $manualTargets = [];
        foreach ($rawTargets as $target) {
            $normalized = $this->normalizeWhatsappTarget($target);
            if ($normalized !== null) {
                $manualTargets[$normalized] = $normalized;
            }
        }

        foreach ($manualTargets as $target) {
            $message = $this->resolveManualTargetWhatsappMessage(
                target: $target,
                template: $template,
                globalMessage: $validated['message'] ?? '',
                useGlobalDefault: $useGlobalDefault,
                messageOverrides: $messageOverrides
            );

            if ($blastMessage === null) {
                $blastMessage = $this->createBlastMessageRecord(
                    channel: 'WHATSAPP',
                    subject: null,
                    fallbackMessage: $validated['message'] ?? '',
                    template: $template
                );
            }

            $blastLog = $this->createBlastLogRecord(
                blastMessage: $blastMessage,
                target: $target,
                messageSnapshot: $message
            );

            $payload = new BlastPayload($message);
            $payload->setMeta('channel', 'WHATSAPP');
            $payload->setMeta('sent_by', Auth::id());
            $payload->setMeta('blast_log_id', $blastLog->id);
            $this->attachFilesToPayload($payload, $attachments);
            $this->attachFilesToPayload(
                $payload,
                $recipientAttachmentOverrides['manual:' . $target] ?? []
            );

            dispatch(new SendWhatsappBlastJob($target, $payload));
        }

        return back()->with('success', 'WhatsApp blast queued.');
    }

    /* =======================
     |  EMAIL
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
            'use_global_default' => 'nullable|boolean',
            'message_overrides' => 'nullable|string',
            'attachment_override_keys' => 'nullable|array',
            'attachment_override_keys.*' => 'nullable|string',
            'attachment_overrides' => 'nullable|array',
            'attachment_overrides.*' => 'nullable|array',
            'attachment_overrides.*.*' => 'nullable|file|max:5120',

            'attachments' => 'nullable|array',
            'attachments.*' => 'nullable|file|max:5120',
        ]);

        $attachments = $this->storeEmailAttachments($request);
        $recipientAttachmentOverrides = $this->storeRecipientAttachmentOverrides(
            $request,
            $validated['attachment_override_keys'] ?? []
        );
        $useGlobalDefault = $request->boolean('use_global_default');
        $messageOverrides = $this->parseMessageOverrides(
            $validated['message_overrides'] ?? null
        );

        $template = null;
        if (!empty($validated['template_id'])) {
            $template = BlastMessageTemplate::query()
                ->where('id', $validated['template_id'])
                ->whereIn('channel', ['EMAIL', 'email'])
                ->where('is_active', true)
                ->first();
        }
        $blastMessage = null;

        if (!empty($validated['recipient_ids'])) {

            $recipients = BlastRecipient::whereIn(
                'id',
                $validated['recipient_ids']
            )
                ->whereNotNull('email_wali')
                ->where('is_valid', true)
                ->get()
                ->filter(
                    fn (BlastRecipient $recipient) =>
                        filter_var($recipient->email_wali, FILTER_VALIDATE_EMAIL)
                )
                ->values();

            foreach ($recipients as $recipient) {
                $message = $this->resolveDbRecipientEmailMessage(
                    recipient: $recipient,
                    renderer: $renderer,
                    template: $template,
                    globalMessage: $validated['message'] ?? '',
                    useGlobalDefault: $useGlobalDefault,
                    messageOverrides: $messageOverrides
                );

                if ($blastMessage === null) {
                    $blastMessage = $this->createBlastMessageRecord(
                        channel: 'EMAIL',
                        subject: $validated['subject'],
                        fallbackMessage: $validated['message'] ?? '',
                        template: $template
                    );
                }

                $blastLog = $this->createBlastLogRecord(
                    blastMessage: $blastMessage,
                    target: $recipient->email_wali,
                    messageSnapshot: $message
                );

                $payload = new BlastPayload($message);
                $payload->setMeta('channel', 'EMAIL');
                $payload->setMeta('sent_by', Auth::id());
                $payload->setMeta('recipient_id', $recipient->id);
                $payload->setMeta('blast_log_id', $blastLog->id);
                $this->attachFilesToPayload($payload, $attachments);
                $this->attachFilesToPayload(
                    $payload,
                    $recipientAttachmentOverrides['db:' . $recipient->id] ?? []
                );

                dispatch(
                    new SendEmailBlastJob(
                        $recipient->email_wali,
                        $validated['subject'],
                        $payload
                    )
                );
            }

        }

        $targets = array_filter(
            array_map('trim', explode(',', $validated['targets'] ?? ''))
        );

        foreach ($targets as $email) {
            $message = $this->resolveManualTargetEmailMessage(
                email: $email,
                template: $template,
                globalMessage: $validated['message'] ?? '',
                useGlobalDefault: $useGlobalDefault,
                messageOverrides: $messageOverrides
            );

            if ($blastMessage === null) {
                $blastMessage = $this->createBlastMessageRecord(
                    channel: 'EMAIL',
                    subject: $validated['subject'],
                    fallbackMessage: $validated['message'] ?? '',
                    template: $template
                );
            }

            $blastLog = $this->createBlastLogRecord(
                blastMessage: $blastMessage,
                target: $email,
                messageSnapshot: $message
            );

            $payload = new BlastPayload($message);
            $payload->setMeta('channel', 'EMAIL');
            $payload->setMeta('sent_by', Auth::id());
            $payload->setMeta('blast_log_id', $blastLog->id);
            $this->attachFilesToPayload($payload, $attachments);
            $this->attachFilesToPayload(
                $payload,
                $recipientAttachmentOverrides['manual:' . strtolower(trim($email))] ?? []
            );

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
     * @param iterable<BlastRecipient> $recipients
     * @return array{
     *     stats: array{total:int, sent:int, failed:int, pending:int},
     *     logs: array<int, array<string, mixed>>
     * }
     */
    private function buildChannelActivityData(
        string $channel,
        iterable $recipients
    ): array {
        $normalizedChannel = strtoupper($channel);

        $statusCounts = BlastLog::query()
            ->whereHas('message', function ($query) use ($normalizedChannel) {
                $query->where('channel', $normalizedChannel);
            })
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $stats = [
            'total' => (int) $statusCounts->sum(),
            'sent' => (int) ($statusCounts['SENT'] ?? 0),
            'failed' => (int) ($statusCounts['FAILED'] ?? 0),
            'pending' => (int) ($statusCounts['PENDING'] ?? 0),
        ];

        $logs = BlastLog::query()
            ->with([
                'message:id,channel,subject',
                'target:id,target',
            ])
            ->whereHas('message', function ($query) use ($normalizedChannel) {
                $query->where('channel', $normalizedChannel);
            })
            ->latest('id')
            ->get();

        $recipientMap = [];
        foreach ($recipients as $recipient) {
            if (!$recipient instanceof BlastRecipient) {
                continue;
            }

            $recipientKey = $normalizedChannel === 'EMAIL'
                ? strtolower(trim((string) $recipient->email_wali))
                : $this->normalizeWhatsappTarget($recipient->wa_wali);

            if (empty($recipientKey)) {
                continue;
            }

            $recipientMap[$recipientKey] = $recipient;
        }

        $mappedLogs = $logs->map(function (BlastLog $log) use (
            $normalizedChannel,
            $recipientMap
        ) {
            $target = trim((string) optional($log->target)->target);
            $targetKey = $normalizedChannel === 'EMAIL'
                ? strtolower($target)
                : ($this->normalizeWhatsappTarget($target) ?? $target);
            $recipient = $recipientMap[$targetKey] ?? null;
            $timestamp = $log->sent_at ?? $log->updated_at ?? $log->created_at;
            $status = strtoupper((string) $log->status);
            $statusKey = match ($status) {
                'SENT' => 'success',
                'FAILED' => 'failed',
                default => 'pending',
            };

            $row = [
                'date' => $timestamp ? $timestamp->format('d/m/Y') : '-',
                'time' => $timestamp ? $timestamp->format('H:i:s') : '-',
                'studentName' => $recipient?->nama_siswa ?: '-',
                'studentClass' => $recipient?->kelas ?: '-',
                'parentName' => $recipient?->nama_wali ?: '-',
                'status' => $statusKey,
            ];

            if ($normalizedChannel === 'EMAIL') {
                $row['email'] = $target !== '' ? $target : '-';
                $subject = trim((string) ($log->message?->subject ?? ''));
                $row['subject'] = $subject !== '' ? $subject : '-';
                $row['attachments'] = '-';
            } else {
                $row['phone'] = $target !== '' ? $target : '-';
            }

            return $row;
        })->values()->all();

        return [
            'stats' => $stats,
            'logs' => $mappedLogs,
        ];
    }

    private function getRecipientsByChannel(string $channel)
    {
        $normalized = strtolower($channel);

        if ($normalized === 'email') {
            return BlastRecipient::query()
                ->whereNotNull('email_wali')
                ->where('is_valid', true)
                ->orderBy('nama_siswa')
                ->get()
                ->filter(
                    fn (BlastRecipient $recipient) =>
                        filter_var($recipient->email_wali, FILTER_VALIDATE_EMAIL)
                )
                ->values();
        }

        return BlastRecipient::query()
            ->whereNotNull('wa_wali')
            ->where('is_valid', true)
            ->orderBy('nama_siswa')
            ->get();
    }

    private function createBlastMessageRecord(
        string $channel,
        ?string $subject,
        string $fallbackMessage,
        ?BlastMessageTemplate $template
    ): BlastMessage {
        $message = trim($fallbackMessage) !== ''
            ? $fallbackMessage
            : (string) ($template?->content ?? '');

        return BlastMessage::query()->create([
            'channel' => strtoupper($channel),
            'subject' => $subject,
            'message' => $message,
            'meta' => [
                'template_id' => $template?->id,
            ],
            'created_by' => Auth::id(),
        ]);
    }

    private function createBlastLogRecord(
        BlastMessage $blastMessage,
        string $target,
        string $messageSnapshot
    ): BlastLog {
        $blastTarget = BlastTarget::query()->create([
            'blast_message_id' => $blastMessage->id,
            'target' => $target,
        ]);

        return BlastLog::query()->create([
            'blast_message_id' => $blastMessage->id,
            'blast_target_id' => $blastTarget->id,
            'status' => 'PENDING',
            'message_snapshot' => $messageSnapshot,
            'error_message' => null,
            'sent_at' => null,
            'attempt' => 0,
        ]);
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
     * @param array<string, string> $overrideKeys
     * @return array<string, BlastAttachment[]>
     */
    private function storeRecipientAttachmentOverrides(
        Request $request,
        array $overrideKeys
    ): array {
        if (
            empty($overrideKeys)
            || !$request->hasFile('attachment_overrides')
        ) {
            return [];
        }

        /** @var array<string, UploadedFile[]|UploadedFile|null> $filesByToken */
        $filesByToken = $request->file('attachment_overrides', []);
        $stored = [];
        $baseFolder = 'blasts/' . Str::uuid();

        foreach ($overrideKeys as $token => $recipientKey) {
            $recipientKey = trim((string) $recipientKey);

            if ($recipientKey === '') {
                continue;
            }

            $files = $filesByToken[$token] ?? [];

            if ($files instanceof UploadedFile) {
                $files = [$files];
            }

            if (!is_array($files) || empty($files)) {
                continue;
            }

            foreach ($files as $file) {
                if (!$file instanceof UploadedFile) {
                    continue;
                }

                $path = $file->store(
                    $baseFolder . '/' . $token,
                    'public'
                );

                $stored[$recipientKey][] = new BlastAttachment(
                    storage_path('app/public/' . $path),
                    $file->getClientOriginalName(),
                    $file->getMimeType() ?: 'application/octet-stream'
                );
            }
        }

        return $stored;
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

    private function parseMessageOverrides(?string $rawOverrides): array
    {
        if (empty($rawOverrides)) {
            return [];
        }

        try {
            $decoded = json_decode($rawOverrides, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable) {
            return [];
        }

        return is_array($decoded) ? $decoded : [];
    }

    private function resolveDbRecipientEmailMessage(
        BlastRecipient $recipient,
        TemplateRenderer $renderer,
        ?BlastMessageTemplate $template,
        string $globalMessage,
        bool $useGlobalDefault,
        array $messageOverrides
    ): string {
        $overrideKey = 'db:' . $recipient->id;
        $override = $messageOverrides[$overrideKey] ?? null;
        $mode = strtolower((string) ($override['mode'] ?? ''));
        $customMessage = trim((string) ($override['message'] ?? ''));

        if ($mode === 'manual' && $customMessage !== '') {
            return $customMessage;
        }

        if ($mode === 'template' && $template) {
            return $renderer->render($template->content, $recipient);
        }

        if ($mode === 'global') {
            return $globalMessage;
        }

        if ($useGlobalDefault && trim($globalMessage) !== '') {
            return $globalMessage;
        }

        if ($template) {
            return $renderer->render($template->content, $recipient);
        }

        return $globalMessage;
    }

    private function resolveManualTargetEmailMessage(
        string $email,
        ?BlastMessageTemplate $template,
        string $globalMessage,
        bool $useGlobalDefault,
        array $messageOverrides
    ): string {
        $overrideKey = 'manual:' . strtolower(trim($email));
        $override = $messageOverrides[$overrideKey] ?? null;
        $mode = strtolower((string) ($override['mode'] ?? ''));
        $customMessage = trim((string) ($override['message'] ?? ''));

        if ($mode === 'manual' && $customMessage !== '') {
            return $customMessage;
        }

        if ($mode === 'template' && $template) {
            return $this->renderManualTemplate($template->content, $email);
        }

        if ($mode === 'global') {
            return $globalMessage;
        }

        if ($useGlobalDefault && trim($globalMessage) !== '') {
            return $globalMessage;
        }

        if ($template) {
            return $this->renderManualTemplate($template->content, $email);
        }

        return $globalMessage;
    }

    private function renderManualTemplate(string $content, string $email): string
    {
        return str_replace(
            ['{{email}}', '{email}'],
            $email,
            $content
        );
    }

    private function resolveDbRecipientWhatsappMessage(
        BlastRecipient $recipient,
        TemplateRenderer $renderer,
        ?BlastMessageTemplate $template,
        string $globalMessage,
        bool $useGlobalDefault,
        array $messageOverrides
    ): string {
        $overrideKey = 'db:' . $recipient->id;
        $override = $messageOverrides[$overrideKey] ?? null;
        $mode = strtolower((string) ($override['mode'] ?? ''));
        $customMessage = trim((string) ($override['message'] ?? ''));

        if ($mode === 'manual' && $customMessage !== '') {
            return $customMessage;
        }

        if ($mode === 'template' && $template) {
            return $renderer->render($template->content, $recipient);
        }

        if ($mode === 'global') {
            return $globalMessage;
        }

        if ($useGlobalDefault && trim($globalMessage) !== '') {
            return $globalMessage;
        }

        if ($template) {
            return $renderer->render($template->content, $recipient);
        }

        return $globalMessage;
    }

    private function resolveManualTargetWhatsappMessage(
        string $target,
        ?BlastMessageTemplate $template,
        string $globalMessage,
        bool $useGlobalDefault,
        array $messageOverrides
    ): string {
        $normalizedTarget = $this->normalizeWhatsappTarget($target) ?? $target;
        $overrideKey = 'manual:' . $normalizedTarget;
        $override = $messageOverrides[$overrideKey] ?? null;
        $mode = strtolower((string) ($override['mode'] ?? ''));
        $customMessage = trim((string) ($override['message'] ?? ''));

        if ($mode === 'manual' && $customMessage !== '') {
            return $customMessage;
        }

        if ($mode === 'template' && $template) {
            return $this->renderManualWhatsappTemplate(
                $template->content,
                $normalizedTarget
            );
        }

        if ($mode === 'global') {
            return $globalMessage;
        }

        if ($useGlobalDefault && trim($globalMessage) !== '') {
            return $globalMessage;
        }

        if ($template) {
            return $this->renderManualWhatsappTemplate(
                $template->content,
                $normalizedTarget
            );
        }

        return $globalMessage;
    }

    private function renderManualWhatsappTemplate(
        string $content,
        string $target
    ): string {
        return str_replace(
            ['{{phone}}', '{phone}', '{{wa}}', '{wa}', '{{whatsapp}}', '{whatsapp}'],
            $target,
            $content
        );
    }

    private function normalizeWhatsappTarget(?string $target): ?string
    {
        if ($target === null) {
            return null;
        }

        $normalized = preg_replace('/\D+/', '', trim($target)) ?? '';
        if ($normalized === '') {
            return null;
        }

        if (Str::startsWith($normalized, '0')) {
            $normalized = '62' . substr($normalized, 1);
        } elseif (Str::startsWith($normalized, '8')) {
            $normalized = '62' . $normalized;
        }

        if (!Str::startsWith($normalized, '62')) {
            return null;
        }

        $length = strlen($normalized);
        if ($length < 10 || $length > 15) {
            return null;
        }

        return $normalized;
    }
}
