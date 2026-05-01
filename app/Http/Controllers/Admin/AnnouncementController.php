<?php

namespace App\Http\Controllers\Admin;

use App\DataTransferObjects\BlastPayload;
use App\Http\Controllers\Controller;
use App\Http\Requests\AnnouncementRequest;
use App\Jobs\Blast\SendEmailBlastJob;
use App\Jobs\Blast\SendWhatsappBlastJob;
use App\Models\Announcement;
use App\Models\AnnouncementLog;
use App\Models\BlastLog;
use App\Models\BlastMessage;
use App\Models\BlastRecipient;
use App\Models\BlastTarget;
use App\Models\Reminder;
use App\Services\Recipient\ContactValueNormalizer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class AnnouncementController extends Controller
{
    public function __construct(
        private ContactValueNormalizer $contactValueNormalizer
    ) {}

    /**
     * Display announcement page.
     */
    public function index()
    {
        return view('admin.announcements.index', $this->buildIndexViewData());
    }

    public function stats(): JsonResponse
    {
        $ids = collect((array) request()->query('ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->values();

        $query = Announcement::query()
            ->withCount([
                'logs as logs_total_count',
                'logs as logs_sent_count' => fn ($builder) => $builder->where('status', 'SENT'),
                'logs as logs_failed_count' => fn ($builder) => $builder->where('status', 'FAILED'),
                'logs as logs_pending_count' => fn ($builder) => $builder->where('status', 'PENDING'),
                'logs as logs_email_total_count' => fn ($builder) => $builder->where('channel', 'EMAIL'),
                'logs as logs_email_opened_count' => fn ($builder) => $builder
                    ->where('channel', 'EMAIL')
                    ->whereNotNull('opened_at'),
            ]);

        if ($ids->isNotEmpty()) {
            $query->whereIn('id', $ids->all());
        }

        $stats = $query
            ->get()
            ->mapWithKeys(function (Announcement $announcement): array {
                $emailTotal = (int) ($announcement->logs_email_total_count ?? 0);
                $emailOpened = (int) ($announcement->logs_email_opened_count ?? 0);

                return [
                    (string) $announcement->id => [
                        'total' => (int) ($announcement->logs_total_count ?? 0),
                        'sent' => (int) ($announcement->logs_sent_count ?? 0),
                        'failed' => (int) ($announcement->logs_failed_count ?? 0),
                        'pending' => (int) ($announcement->logs_pending_count ?? 0),
                        'email_total' => $emailTotal,
                        'email_opened' => $emailOpened,
                        'open_rate' => $emailTotal > 0
                            ? round(($emailOpened / $emailTotal) * 100, 1)
                            : 0,
                    ],
                ];
            });

        return response()->json([
            'stats' => $stats,
            'generated_at' => now()->toIso8601String(),
        ]);
    }

    /**
     * Keep backward compatibility with old route.
     */
    public function create()
    {
        return redirect()->route('admin.announcements.index');
    }

    public function store(AnnouncementRequest $request)
    {
        $validated = $request->validated();

        $announcement = Announcement::query()->create([
            'title' => $validated['title'],
            'message' => $validated['message'],
            'attachment_path' => $request->hasFile('attachment')
                ? $request->file('attachment')->store('announcements', 'public')
                : null,
            'created_by' => (string) Auth::id(),
        ]);

        $linkedReminder = $this->linkReminderToAnnouncement(
            reminderId: (int) ($validated['reminder_id'] ?? 0),
            announcement: $announcement
        );

        $dispatchResult = $this->dispatchAnnouncementToBlast(
            $announcement,
            $validated['channels'] ?? []
        );

        $linkMessage = $linkedReminder
            ? ' Reminder #' . $linkedReminder->id . ' berhasil ditautkan.'
            : '';

        return redirect()
            ->route('admin.announcements.index')
            ->with('success', $this->buildDispatchMessage('Pengumuman berhasil dibuat.' . $linkMessage, $dispatchResult));
    }

    public function edit(int $id)
    {
        $editingAnnouncement = Announcement::query()
            ->with([
                'reminders' => fn ($query) => $query
                    ->select([
                        'id',
                        'announcement_id',
                        'title',
                        'description',
                        'remind_at',
                        'is_active',
                        'alert_before_minutes',
                        'created_at',
                    ])
                    ->orderByDesc('remind_at'),
            ])
            ->findOrFail($id);
        $viewData = $this->buildIndexViewData();
        $viewData['editingAnnouncement'] = $editingAnnouncement;
        $viewData['focusedAnnouncementId'] = $editingAnnouncement->id;

        return view('admin.announcements.index', $viewData);
    }

    public function update(AnnouncementRequest $request, int $id)
    {
        $announcement = Announcement::query()->findOrFail($id);
        $validated = $request->validated();

        $updatePayload = [
            'title' => $validated['title'],
            'message' => $validated['message'],
        ];

        if ($request->hasFile('attachment')) {
            $updatePayload['attachment_path'] = $request->file('attachment')->store('announcements', 'public');
        }

        $announcement->update($updatePayload);

        $dispatchResult = $this->dispatchAnnouncementToBlast(
            $announcement,
            $validated['channels'] ?? []
        );

        return redirect()
            ->route('admin.announcements.index')
            ->with('success', $this->buildDispatchMessage('Pengumuman berhasil diperbarui.', $dispatchResult));
    }

    public function destroy(int $id)
    {
        $announcement = Announcement::query()->findOrFail($id);
        $announcement->delete();

        return redirect()
            ->route('admin.announcements.index')
            ->with('success', 'Pengumuman berhasil dihapus.');
    }

    private function buildIndexViewData(): array
    {
        $search = trim((string) request()->query('search', ''));
        $focusedReminderId = (int) request()->query('focus_reminder', 0);
        $focusedAnnouncementId = (int) request()->query('focus_announcement', 0);
        $focusedReminder = null;

        if ($focusedReminderId > 0) {
            $focusedReminder = Reminder::query()
                ->with(['creator:id,name', 'announcement:id,title'])
                ->whereKey($focusedReminderId)
                ->where('type', 'ANNOUNCEMENT')
                ->first();
        }

        if ($focusedReminder && $focusedAnnouncementId <= 0) {
            $focusedAnnouncementId = (int) ($focusedReminder->announcement_id ?? 0);
        }

        $announcementsQuery = Announcement::query()
            ->with([
                'creator:id,name',
                'logs' => fn ($query) => $query->latest('id'),
                'reminders' => fn ($query) => $query
                    ->select([
                        'id',
                        'announcement_id',
                        'title',
                        'remind_at',
                        'is_active',
                        'alert_before_minutes',
                        'created_at',
                    ])
                    ->orderByDesc('remind_at'),
            ])
            ->withCount([
                'logs as logs_total_count',
                'logs as logs_sent_count' => fn ($query) => $query->where('status', 'SENT'),
                'logs as logs_failed_count' => fn ($query) => $query->where('status', 'FAILED'),
                'logs as logs_pending_count' => fn ($query) => $query->where('status', 'PENDING'),
                'logs as logs_email_total_count' => fn ($query) => $query->where('channel', 'EMAIL'),
                'logs as logs_email_opened_count' => fn ($query) => $query
                    ->where('channel', 'EMAIL')
                    ->whereNotNull('opened_at'),
                'reminders as reminders_total_count',
                'reminders as reminders_active_count' => fn ($query) => $query->where('is_active', true),
            ]);

        if ($search !== '') {
            $announcementsQuery->where(function ($query) use ($search) {
                $query->where('title', 'like', '%' . $search . '%')
                    ->orWhere('message', 'like', '%' . $search . '%')
                    ->orWhereHas('creator', function ($creatorQuery) use ($search) {
                        $creatorQuery->where('name', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('reminders', function ($reminderQuery) use ($search) {
                        $reminderQuery->where('title', 'like', '%' . $search . '%')
                            ->orWhere('description', 'like', '%' . $search . '%');
                    });
            });
        }

        $announcements = $announcementsQuery
            ->latest('id')
            ->paginate(10, ['*'], 'announcement_page')
            ->withQueryString();

        $announcementLogsQuery = AnnouncementLog::query()
            ->with('announcement:id,title');

        if ($search !== '') {
            $announcementLogsQuery->where(function ($query) use ($search) {
                $query->where('channel', 'like', '%' . $search . '%')
                    ->orWhere('target', 'like', '%' . $search . '%')
                    ->orWhere('status', 'like', '%' . $search . '%')
                    ->orWhere('response', 'like', '%' . $search . '%')
                    ->orWhereHas('announcement', function ($announcementQuery) use ($search) {
                        $announcementQuery->where('title', 'like', '%' . $search . '%');
                    });
            });
        }

        $announcementLogs = $announcementLogsQuery
            ->latest('id')
            ->paginate(15, ['*'], 'log_page')
            ->withQueryString();

        $emailRecipientCount = BlastRecipient::query()
            ->whereNotNull('email_wali')
            ->where('is_valid', true)
            ->get()
            ->filter(
                fn (BlastRecipient $recipient) =>
                    filter_var($recipient->email_wali, FILTER_VALIDATE_EMAIL)
            )
            ->count();

        $whatsappRecipientCount = BlastRecipient::query()
            ->where(function ($query) {
                $query->whereNotNull('wa_wali')
                    ->orWhereNotNull('wa_wali_2');
            })
            ->where('is_valid', true)
            ->get()
            ->filter(
                fn (BlastRecipient $recipient) =>
                    !empty($this->collectWhatsappTargets($recipient))
            )
            ->count();

        $pendingAnnouncementReminders = Reminder::query()
            ->with('creator:id,name')
            ->select([
                'id',
                'title',
                'description',
                'remind_at',
                'is_active',
                'announcement_id',
                'created_by',
            ])
            ->where('type', 'ANNOUNCEMENT')
            ->whereNull('announcement_id')
            ->orderByDesc('is_active')
            ->orderBy('remind_at')
            ->limit(10)
            ->get();

        return [
            'announcements' => $announcements,
            'announcementLogs' => $announcementLogs,
            'emailRecipientCount' => $emailRecipientCount,
            'whatsappRecipientCount' => $whatsappRecipientCount,
            'focusedReminder' => $focusedReminder,
            'pendingAnnouncementReminders' => $pendingAnnouncementReminders,
            'search' => $search,
            'focusedReminderId' => $focusedReminderId,
            'focusedAnnouncementId' => $focusedAnnouncementId,
            'editingAnnouncement' => null,
        ];
    }

    private function linkReminderToAnnouncement(int $reminderId, Announcement $announcement): ?Reminder
    {
        if ($reminderId <= 0) {
            return null;
        }

        $reminder = Reminder::query()
            ->whereKey($reminderId)
            ->where('type', 'ANNOUNCEMENT')
            ->first();

        if (! $reminder) {
            return null;
        }

        $reminder->update([
            'announcement_id' => $announcement->id,
        ]);

        return $reminder;
    }

    /**
     * @param array<int, string> $channels
     * @return array{
     *     channels: array<int, string>,
     *     total_targets: int,
     *     email_targets: int,
     *     whatsapp_targets: int
     * }
     */
    private function dispatchAnnouncementToBlast(
        Announcement $announcement,
        array $channels
    ): array {
        $channels = array_values(
            array_unique(
                array_filter(
                    $channels,
                    fn ($channel) => in_array($channel, ['email', 'whatsapp'], true)
                )
            )
        );

        $result = [
            'channels' => $channels,
            'total_targets' => 0,
            'email_targets' => 0,
            'whatsapp_targets' => 0,
        ];

        if (empty($channels)) {
            return $result;
        }

        $messageBody = $this->formatAnnouncementMessage($announcement);

        if (in_array('email', $channels, true)) {
            $subject = '[Announcement] ' . $announcement->title;

            $recipients = BlastRecipient::query()
                ->whereNotNull('email_wali')
                ->where('is_valid', true)
                ->get()
                ->filter(
                    fn (BlastRecipient $recipient) =>
                        filter_var($recipient->email_wali, FILTER_VALIDATE_EMAIL)
                )
                ->values();

            $blastMessage = null;
            foreach ($recipients as $recipient) {
                $target = trim((string) $recipient->email_wali);
                if ($target === '') {
                    continue;
                }

                if ($blastMessage === null) {
                    $blastMessage = $this->createBlastMessageRecord(
                        channel: 'EMAIL',
                        subject: $subject,
                        message: $messageBody,
                        announcement: $announcement
                    );
                }

                $blastLog = $this->createBlastLogRecord($blastMessage, $target, $messageBody);
                $announcementLog = $this->createAnnouncementLogRecord($announcement, 'EMAIL', $target);

                $payload = new BlastPayload($messageBody);
                $payload->setMeta('channel', 'EMAIL');
                $payload->setMeta('sent_by', Auth::id());
                $payload->setMeta('recipient_id', $recipient->id);
                $payload->setMeta('blast_log_id', $blastLog->id);
                $payload->setMeta('blast_message_id', $blastMessage->id);
                $payload->setMeta('announcement_id', $announcement->id);
                $payload->setMeta('announcement_log_id', $announcementLog->id);
                $payload->setMeta('announcement_track_token', $announcementLog->track_token);

                dispatch(new SendEmailBlastJob($target, $subject, $payload));

                $result['email_targets']++;
                $result['total_targets']++;
            }
        }

        if (in_array('whatsapp', $channels, true)) {
            $recipients = BlastRecipient::query()
                ->where(function ($query) {
                    $query->whereNotNull('wa_wali')
                        ->orWhereNotNull('wa_wali_2');
                })
                ->where('is_valid', true)
                ->get();

            $blastMessage = null;
            foreach ($recipients as $recipient) {
                $targets = $this->collectWhatsappTargets($recipient);
                if (empty($targets)) {
                    continue;
                }

                if ($blastMessage === null) {
                    $blastMessage = $this->createBlastMessageRecord(
                        channel: 'WHATSAPP',
                        subject: null,
                        message: $messageBody,
                        announcement: $announcement
                    );
                }

                foreach ($targets as $target) {
                    $blastLog = $this->createBlastLogRecord($blastMessage, $target, $messageBody);
                    $announcementLog = $this->createAnnouncementLogRecord($announcement, 'WHATSAPP', $target);

                    $payload = new BlastPayload($messageBody);
                    $payload->setMeta('channel', 'WHATSAPP');
                    $payload->setMeta('sent_by', Auth::id());
                    $payload->setMeta('recipient_id', $recipient->id);
                    $payload->setMeta('blast_log_id', $blastLog->id);
                    $payload->setMeta('blast_message_id', $blastMessage->id);
                    $payload->setMeta('announcement_id', $announcement->id);
                    $payload->setMeta('announcement_log_id', $announcementLog->id);

                    dispatch(new SendWhatsappBlastJob($target, $payload));

                    $result['whatsapp_targets']++;
                    $result['total_targets']++;
                }
            }
        }

        return $result;
    }

    private function buildDispatchMessage(string $baseMessage, array $dispatchResult): string
    {
        if (empty($dispatchResult['channels'])) {
            return $baseMessage . ' Tidak ada channel blasting yang dipilih.';
        }

        $selectedChannels = implode(', ', $dispatchResult['channels']);

        return sprintf(
            '%s Blast channel: %s. Target queue: %d (email: %d, whatsapp: %d).',
            $baseMessage,
            $selectedChannels,
            (int) $dispatchResult['total_targets'],
            (int) $dispatchResult['email_targets'],
            (int) $dispatchResult['whatsapp_targets']
        );
    }

    private function formatAnnouncementMessage(Announcement $announcement): string
    {
        return trim($announcement->title) . "\n\n" . trim($announcement->message);
    }

    private function createBlastMessageRecord(
        string $channel,
        ?string $subject,
        string $message,
        Announcement $announcement
    ): BlastMessage {
        return BlastMessage::query()->create([
            'channel' => strtoupper($channel),
            'subject' => $subject,
            'message' => $message,
            'meta' => [
                'source' => 'announcement',
                'announcement_id' => $announcement->id,
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

    private function createAnnouncementLogRecord(
        Announcement $announcement,
        string $channel,
        string $target
    ): AnnouncementLog {
        return AnnouncementLog::query()->create([
            'announcement_id' => $announcement->id,
            'channel' => strtoupper($channel),
            'target' => $target,
            'status' => 'PENDING',
            'response' => null,
            'track_token' => strtoupper($channel) === 'EMAIL' ? (string) Str::uuid() : null,
            'opened_at' => null,
            'open_count' => 0,
            'sent_at' => null,
        ]);
    }

    /**
     * @return string[]
     */
    private function collectWhatsappTargets(BlastRecipient $recipient): array
    {
        $targets = [];
        foreach ([$recipient->wa_wali, $recipient->wa_wali_2] as $candidate) {
            $normalized = $this->normalizeWhatsappTarget($candidate);
            if ($normalized === null) {
                continue;
            }

            $targets[$normalized] = $normalized;
        }

        return array_values($targets);
    }

    private function normalizeWhatsappTarget(?string $target): ?string
    {
        if ($target === null) {
            return null;
        }

        $result = $this->contactValueNormalizer->normalizeWhatsapp($target);

        if ($result['value'] === null) {
            return null;
        }

        return $result['value'];
    }
}
