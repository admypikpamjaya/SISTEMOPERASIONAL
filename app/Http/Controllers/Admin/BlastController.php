<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\DataTransferObjects\BlastPayload;
use App\DataTransferObjects\BlastAttachment;
use App\Jobs\Blast\QueueBlastDeliveryJob;
use App\Models\BlastRecipient;
use App\Models\BlastMessageTemplate;
use App\Models\BlastMessage;
use App\Models\BlastTarget;
use App\Models\BlastLog;
use App\Models\Announcement;
use App\Services\Blast\TemplateRenderer;
use App\Services\Blast\RecipientSelectorService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Carbon\Carbon;
use Carbon\CarbonInterface;
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

        $announcementOptions = Announcement::query()
            ->latest('id')
            ->limit(100)
            ->get(['id', 'title', 'message']);

        $activityData = $this->buildChannelActivityData('WHATSAPP', $recipients);
        $activityLogs = $activityData['logs'];
        $activityStats = $activityData['stats'];

        return view('admin.blast.whatsapp', compact(
            'recipients',
            'templates',
            'announcementOptions',
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

        $announcementOptions = Announcement::query()
            ->latest('id')
            ->limit(100)
            ->get(['id', 'title', 'message']);

        $activityData = $this->buildChannelActivityData('EMAIL', $recipients);
        $activityLogs = $activityData['logs'];
        $activityStats = $activityData['stats'];

        return view(
            'admin.blast.email',
            compact('recipients', 'templates', 'announcementOptions', 'activityLogs', 'activityStats')
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
            'scheduled_at' => 'nullable|date',
            'rate_limit_per_minute' => 'nullable|integer|min:1|max:5000',
            'batch_size' => 'nullable|integer|min:1|max:2000',
            'batch_delay_seconds' => 'nullable|integer|min:0|max:3600',
            'retry_attempts' => 'nullable|integer|min:1|max:10',
            'retry_backoff_seconds' => 'nullable|string|max:255',
            'priority' => 'nullable|in:high,normal,low',
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
        $campaignOptions = $this->resolveCampaignOptions(
            validatedData: $validated,
            channel: 'WHATSAPP'
        );
        $dispatchIndex = 0;

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
                        template: $template,
                        campaignOptions: $campaignOptions
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
                $payload->setMeta('blast_message_id', $blastMessage->id);
                $payload->setMeta('queue_name', $campaignOptions['queue_name']);
                $payload->setMeta('retry_attempts', $campaignOptions['retry_attempts']);
                $payload->setMeta('retry_backoff_seconds', $campaignOptions['retry_backoff_seconds']);
                $this->attachFilesToPayload($payload, $attachments);
                $this->attachFilesToPayload(
                    $payload,
                    $recipientAttachmentOverrides['db:' . $recipient->id] ?? []
                );

                $this->dispatchQueuedBlastDelivery(
                    channel: 'WHATSAPP',
                    target: $target,
                    subject: null,
                    payload: $payload,
                    campaignOptions: $campaignOptions,
                    dispatchIndex: $dispatchIndex
                );
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
                    template: $template,
                    campaignOptions: $campaignOptions
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
            $payload->setMeta('blast_message_id', $blastMessage->id);
            $payload->setMeta('queue_name', $campaignOptions['queue_name']);
            $payload->setMeta('retry_attempts', $campaignOptions['retry_attempts']);
            $payload->setMeta('retry_backoff_seconds', $campaignOptions['retry_backoff_seconds']);
            $this->attachFilesToPayload($payload, $attachments);
            $this->attachFilesToPayload(
                $payload,
                $recipientAttachmentOverrides['manual:' . $target] ?? []
            );

            $this->dispatchQueuedBlastDelivery(
                channel: 'WHATSAPP',
                target: $target,
                subject: null,
                payload: $payload,
                campaignOptions: $campaignOptions,
                dispatchIndex: $dispatchIndex
            );
        }

        $campaignId = $blastMessage?->id;
        $statusMessage = 'WhatsApp blast queued.';
        if ($campaignId !== null) {
            $statusMessage .= ' Campaign ID: ' . $campaignId;
        }

        return back()
            ->with('success', $statusMessage)
            ->with('campaign_id', $campaignId);
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
            'scheduled_at' => 'nullable|date',
            'rate_limit_per_minute' => 'nullable|integer|min:1|max:5000',
            'batch_size' => 'nullable|integer|min:1|max:2000',
            'batch_delay_seconds' => 'nullable|integer|min:0|max:3600',
            'retry_attempts' => 'nullable|integer|min:1|max:10',
            'retry_backoff_seconds' => 'nullable|string|max:255',
            'priority' => 'nullable|in:high,normal,low',
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
        $campaignOptions = $this->resolveCampaignOptions(
            validatedData: $validated,
            channel: 'EMAIL'
        );
        $dispatchIndex = 0;

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
                        template: $template,
                        campaignOptions: $campaignOptions
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
                $payload->setMeta('blast_message_id', $blastMessage->id);
                $payload->setMeta('queue_name', $campaignOptions['queue_name']);
                $payload->setMeta('retry_attempts', $campaignOptions['retry_attempts']);
                $payload->setMeta('retry_backoff_seconds', $campaignOptions['retry_backoff_seconds']);
                $this->attachFilesToPayload($payload, $attachments);
                $this->attachFilesToPayload(
                    $payload,
                    $recipientAttachmentOverrides['db:' . $recipient->id] ?? []
                );

                $this->dispatchQueuedBlastDelivery(
                    channel: 'EMAIL',
                    target: $recipient->email_wali,
                    subject: $validated['subject'],
                    payload: $payload,
                    campaignOptions: $campaignOptions,
                    dispatchIndex: $dispatchIndex
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
                    template: $template,
                    campaignOptions: $campaignOptions
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
            $payload->setMeta('blast_message_id', $blastMessage->id);
            $payload->setMeta('queue_name', $campaignOptions['queue_name']);
            $payload->setMeta('retry_attempts', $campaignOptions['retry_attempts']);
            $payload->setMeta('retry_backoff_seconds', $campaignOptions['retry_backoff_seconds']);
            $this->attachFilesToPayload($payload, $attachments);
            $this->attachFilesToPayload(
                $payload,
                $recipientAttachmentOverrides['manual:' . strtolower(trim($email))] ?? []
            );

            $this->dispatchQueuedBlastDelivery(
                channel: 'EMAIL',
                target: $email,
                subject: $validated['subject'],
                payload: $payload,
                campaignOptions: $campaignOptions,
                dispatchIndex: $dispatchIndex
            );
        }

        $campaignId = $blastMessage?->id;
        $statusMessage = 'Email blast queued.';
        if ($campaignId !== null) {
            $statusMessage .= ' Campaign ID: ' . $campaignId;
        }

        return back()
            ->with('success', $statusMessage)
            ->with('campaign_id', $campaignId);
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

    public function campaigns(Request $request)
    {
        $validated = $request->validate([
            'channel' => 'required|in:email,whatsapp',
            'q' => 'nullable|string|max:100',
        ]);

        $channel = strtoupper((string) $validated['channel']);
        $search = trim((string) ($validated['q'] ?? ''));

        $query = BlastMessage::query()
            ->where('channel', $channel)
            ->withCount([
                'logs as logs_total_count',
                'logs as logs_sent_count' => function ($query) {
                    $query->where('status', 'SENT');
                },
                'logs as logs_failed_count' => function ($query) {
                    $query->where('status', 'FAILED');
                },
                'logs as logs_pending_count' => function ($query) {
                    $query->where('status', 'PENDING');
                },
            ]);

        if ($search !== '') {
            $query->where('id', 'like', '%' . $search . '%');
        }

        $campaigns = $query
            ->latest('created_at')
            ->limit(25)
            ->get(['id', 'channel', 'campaign_status', 'priority', 'scheduled_at', 'created_at'])
            ->map(function (BlastMessage $campaign) {
                return [
                    'id' => $campaign->id,
                    'channel' => strtoupper((string) $campaign->channel),
                    'status' => strtoupper((string) $campaign->campaign_status),
                    'priority' => strtolower((string) $campaign->priority),
                    'scheduledAt' => $campaign->scheduled_at?->format('Y-m-d H:i:s'),
                    'createdAt' => $campaign->created_at?->format('Y-m-d H:i:s'),
                    'stats' => [
                        'total' => (int) ($campaign->logs_total_count ?? 0),
                        'sent' => (int) ($campaign->logs_sent_count ?? 0),
                        'failed' => (int) ($campaign->logs_failed_count ?? 0),
                        'pending' => (int) ($campaign->logs_pending_count ?? 0),
                    ],
                ];
            })
            ->values();

        return response()->json([
            'campaigns' => $campaigns,
        ]);
    }

    public function pauseCampaign(Request $request)
    {
        $validated = $request->validate([
            'campaign_id' => 'required|string|exists:blast_messages,id',
        ]);

        $campaign = BlastMessage::query()->findOrFail($validated['campaign_id']);
        $campaign->update([
            'campaign_status' => 'PAUSED',
            'paused_at' => now(),
            'completed_at' => null,
        ]);

        return back()
            ->with('success', 'Campaign paused. ID: ' . $campaign->id)
            ->with('campaign_id', $campaign->id);
    }

    public function resumeCampaign(Request $request)
    {
        $validated = $request->validate([
            'campaign_id' => 'required|string|exists:blast_messages,id',
        ]);

        $campaign = BlastMessage::query()->findOrFail($validated['campaign_id']);

        $nextStatus = $campaign->scheduled_at instanceof CarbonInterface
            && $campaign->scheduled_at->isFuture()
            ? 'SCHEDULED'
            : 'RUNNING';

        $campaign->update([
            'campaign_status' => $nextStatus,
            'paused_at' => null,
            'started_at' => $campaign->started_at ?? now(),
        ]);

        return back()
            ->with('success', 'Campaign resumed. ID: ' . $campaign->id)
            ->with('campaign_id', $campaign->id);
    }

    public function stopCampaign(Request $request)
    {
        $validated = $request->validate([
            'campaign_id' => 'required|string|exists:blast_messages,id',
        ]);

        $campaign = BlastMessage::query()->findOrFail($validated['campaign_id']);
        $campaign->update([
            'campaign_status' => 'STOPPED',
            'completed_at' => now(),
            'paused_at' => null,
        ]);

        BlastLog::query()
            ->where('blast_message_id', $campaign->id)
            ->where('status', 'PENDING')
            ->update([
                'status' => 'FAILED',
                'error_message' => 'Campaign stopped by operator.',
                'sent_at' => now(),
            ]);

        return back()
            ->with('success', 'Campaign stopped. ID: ' . $campaign->id)
            ->with('campaign_id', $campaign->id);
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
                'campaignId' => (string) $log->blast_message_id,
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
        ?BlastMessageTemplate $template,
        array $campaignOptions
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
                'campaign' => [
                    'scheduled_at' => $campaignOptions['scheduled_at'] instanceof CarbonInterface
                        ? $campaignOptions['scheduled_at']->toIso8601String()
                        : null,
                    'rate_limit_per_minute' => $campaignOptions['rate_limit_per_minute'],
                    'batch_size' => $campaignOptions['batch_size'],
                    'batch_delay_seconds' => $campaignOptions['batch_delay_seconds'],
                    'retry_attempts' => $campaignOptions['retry_attempts'],
                    'retry_backoff_seconds' => $campaignOptions['retry_backoff_seconds'],
                    'priority' => $campaignOptions['priority'],
                    'queue_name' => $campaignOptions['queue_name'],
                ],
            ],
            'campaign_status' => $campaignOptions['initial_status'],
            'priority' => $campaignOptions['priority'],
            'scheduled_at' => $campaignOptions['scheduled_at'],
            'started_at' => null,
            'paused_at' => null,
            'completed_at' => null,
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

    private function resolveCampaignOptions(array $validatedData, string $channel): array
    {
        $normalizedChannel = strtolower(trim($channel));
        $normalizedPriority = strtolower((string) ($validatedData['priority'] ?? 'normal'));
        if (!in_array($normalizedPriority, ['high', 'normal', 'low'], true)) {
            $normalizedPriority = 'normal';
        }

        $scheduledAt = null;
        if (!empty($validatedData['scheduled_at'])) {
            $scheduledAt = Carbon::parse((string) $validatedData['scheduled_at'], config('app.timezone'));
        }

        $queueName = $this->resolveQueueName($normalizedChannel, $normalizedPriority);

        $rateLimitKey = $normalizedChannel === 'email'
            ? 'blast.rate_limits.email_per_minute'
            : 'blast.rate_limits.whatsapp_per_minute';

        $rateLimitPerMinute = max(
            1,
            (int) ($validatedData['rate_limit_per_minute'] ?? config($rateLimitKey, 60))
        );

        $batchSize = max(
            1,
            (int) ($validatedData['batch_size'] ?? config('blast.batch.size', 50))
        );

        $batchDelaySeconds = max(
            0,
            (int) ($validatedData['batch_delay_seconds'] ?? config('blast.batch.delay_seconds', 10))
        );

        $retryAttempts = max(
            1,
            (int) ($validatedData['retry_attempts'] ?? config('blast.retry.max_attempts', 3))
        );

        $retryBackoffSeconds = $this->parseRetryBackoffSeconds(
            $validatedData['retry_backoff_seconds'] ?? null
        );

        $scheduledDelaySeconds = 0;
        if ($scheduledAt instanceof CarbonInterface) {
            $scheduledDelaySeconds = max(
                0,
                now()->diffInSeconds($scheduledAt, false)
            );
        }

        return [
            'priority' => $normalizedPriority,
            'queue_name' => $queueName,
            'scheduled_at' => $scheduledAt,
            'scheduled_delay_seconds' => $scheduledDelaySeconds,
            'rate_limit_per_minute' => $rateLimitPerMinute,
            'batch_size' => $batchSize,
            'batch_delay_seconds' => $batchDelaySeconds,
            'retry_attempts' => $retryAttempts,
            'retry_backoff_seconds' => $retryBackoffSeconds,
            'initial_status' => $scheduledDelaySeconds > 0 ? 'SCHEDULED' : 'QUEUED',
        ];
    }

    private function resolveQueueName(string $channel, string $priority): string
    {
        $configured = config("blast.queues.{$channel}.{$priority}");
        if (is_string($configured) && trim($configured) !== '') {
            return trim($configured);
        }

        return 'blast-' . $channel . '-' . $priority;
    }

    /**
     * @return int[]
     */
    private function parseRetryBackoffSeconds(?string $rawValue): array
    {
        if ($rawValue === null || trim($rawValue) === '') {
            $default = config('blast.retry.backoff_seconds', [30, 120, 300]);
            return is_array($default) ? $default : [30, 120, 300];
        }

        $parts = preg_split('/\s*,\s*/', trim($rawValue)) ?: [];
        $seconds = [];
        foreach ($parts as $part) {
            if ($part === '') {
                continue;
            }

            $value = (int) $part;
            if ($value < 0) {
                continue;
            }

            $seconds[] = $value;
        }

        if ($seconds === []) {
            $default = config('blast.retry.backoff_seconds', [30, 120, 300]);
            return is_array($default) ? $default : [30, 120, 300];
        }

        return array_values(array_unique($seconds));
    }

    private function dispatchQueuedBlastDelivery(
        string $channel,
        string $target,
        ?string $subject,
        BlastPayload $payload,
        array $campaignOptions,
        int &$dispatchIndex
    ): void {
        $job = new QueueBlastDeliveryJob(
            $channel,
            $target,
            $subject,
            $payload
        );

        if (!empty($campaignOptions['queue_name'])) {
            $job->onQueue((string) $campaignOptions['queue_name']);
        }

        $delaySeconds = $this->calculateDispatchDelaySeconds(
            $campaignOptions,
            $dispatchIndex
        );
        if ($delaySeconds > 0) {
            $job->delay(now()->addSeconds($delaySeconds));
        }

        dispatch($job);
        $dispatchIndex++;
    }

    private function calculateDispatchDelaySeconds(
        array $campaignOptions,
        int $dispatchIndex
    ): int {
        $scheduledDelaySeconds = max(
            0,
            (int) ($campaignOptions['scheduled_delay_seconds'] ?? 0)
        );

        $rateLimit = max(
            1,
            (int) ($campaignOptions['rate_limit_per_minute'] ?? 1)
        );

        $batchSize = max(
            1,
            (int) ($campaignOptions['batch_size'] ?? 1)
        );

        $batchDelaySeconds = max(
            0,
            (int) ($campaignOptions['batch_delay_seconds'] ?? 0)
        );

        $rateDelaySeconds = (int) floor(($dispatchIndex * 60) / $rateLimit);
        $batchIndex = intdiv($dispatchIndex, $batchSize);
        $batchDelay = $batchIndex * $batchDelaySeconds;

        return $scheduledDelaySeconds + $rateDelaySeconds + $batchDelay;
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
