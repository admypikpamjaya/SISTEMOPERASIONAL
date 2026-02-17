<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReminderStoreRequest;
use App\Models\Announcement;
use App\Models\Reminder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReminderController extends Controller
{
    private const WIB_TIMEZONE = 'Asia/Jakarta';

    public function index()
    {
        return view('admin.reminders.index', $this->buildReminderPageData());
    }

    public function edit(Reminder $reminder)
    {
        return view('admin.reminders.index', $this->buildReminderPageData($reminder));
    }

    public function store(ReminderStoreRequest $request)
    {
        $validated = $request->validated();
        $isAnnouncementReminder = $validated['type'] === 'ANNOUNCEMENT';
        $remindAt = Carbon::parse((string) $validated['remind_at'], self::WIB_TIMEZONE);

        Reminder::query()->create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'remind_at' => $remindAt,
            'alert_before_minutes' => (int) $validated['alert_before_minutes'],
            'type' => $validated['type'],
            'announcement_id' => $isAnnouncementReminder
                ? ($validated['announcement_id'] ?? null)
                : null,
            'is_active' => true,
            'created_by' => (string) Auth::id(),
        ]);

        return redirect()
            ->route('admin.reminders.index')
            ->with('success', 'Reminder berhasil dibuat.');
    }

    public function update(ReminderStoreRequest $request, Reminder $reminder)
    {
        $validated = $request->validated();
        $isAnnouncementReminder = $validated['type'] === 'ANNOUNCEMENT';
        $remindAt = Carbon::parse((string) $validated['remind_at'], self::WIB_TIMEZONE);

        $reminder->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'remind_at' => $remindAt,
            'alert_before_minutes' => (int) $validated['alert_before_minutes'],
            'type' => $validated['type'],
            'announcement_id' => $isAnnouncementReminder
                ? ($validated['announcement_id'] ?? null)
                : null,
        ]);

        return redirect()
            ->route('admin.reminders.index')
            ->with('success', 'Reminder berhasil diperbarui.');
    }

    public function toggle(Request $request, Reminder $reminder)
    {
        $validated = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        $isActive = (bool) $validated['is_active'];

        $reminder->update([
            'is_active' => $isActive,
            'deactivated_at' => $isActive ? null : now(self::WIB_TIMEZONE),
            'deactivated_by' => $isActive ? null : (string) Auth::id(),
        ]);

        $message = $isActive
            ? 'Reminder berhasil diaktifkan.'
            : 'Reminder berhasil dinonaktifkan.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'data' => [
                    'id' => $reminder->id,
                    'is_active' => $reminder->is_active,
                ],
            ]);
        }

        return redirect()
            ->route('admin.reminders.index')
            ->with('success', $message);
    }

    public function alerts()
    {
        $now = now(self::WIB_TIMEZONE);

        $reminders = Reminder::query()
            ->with('announcement:id,title')
            ->where('is_active', true)
            ->where('remind_at', '<=', $now->copy()->addDays(7))
            ->orderBy('remind_at')
            ->limit(100)
            ->get();

        $alerts = $reminders
            ->map(function (Reminder $reminder) use ($now) {
                $state = $reminder->alertState($now);
                if ($state === null) {
                    return null;
                }

                $remindAt = $reminder->remind_at?->copy()->timezone(self::WIB_TIMEZONE);
                $minutesDifference = $now->diffInMinutes($remindAt, false);
                $minutesUntilDue = max(0, $minutesDifference);
                $minutesOverdue = abs(min(0, $minutesDifference));

                $hint = $state === 'upcoming'
                    ? ($minutesUntilDue <= 1
                        ? 'Kurang dari 1 menit menuju waktu reminder.'
                        : 'Waktu reminder dalam ' . $minutesUntilDue . ' menit.')
                    : ($minutesOverdue <= 1
                        ? 'Sudah masuk waktu reminder (hari-H).'
                        : 'Sudah lewat ' . $minutesOverdue . ' menit dari jadwal reminder.');

                $announcementUrl = null;
                if ($reminder->isAnnouncementType()) {
                    $announcementUrl = $reminder->announcement_id
                        ? route('admin.announcements.edit', [
                            'id' => $reminder->announcement_id,
                            'focus_reminder' => $reminder->id,
                            'focus_announcement' => $reminder->announcement_id,
                        ])
                        : route('admin.announcements.index', [
                            'focus_reminder' => $reminder->id,
                        ]);
                }

                return [
                    'id' => $reminder->id,
                    'title' => $reminder->title,
                    'description' => $reminder->description,
                    'type' => $reminder->type,
                    'state' => $state,
                    'hint' => $hint,
                    'remind_at' => $remindAt?->toIso8601String(),
                    'remind_at_label' => $remindAt?->format('d/m/Y H:i'),
                    'announcement_id' => $reminder->announcement_id,
                    'announcement_title' => $reminder->announcement?->title,
                    'announcement_url' => $announcementUrl,
                ];
            })
            ->filter()
            ->values();

        return response()->json([
            'server_time' => $now->toIso8601String(),
            'alerts' => $alerts,
        ]);
    }

    private function buildReminderPageData(?Reminder $editingReminder = null): array
    {
        $announcements = Announcement::query()
            ->select('id', 'title', 'created_at')
            ->latest('id')
            ->limit(100)
            ->get();

        if ($editingReminder && $editingReminder->announcement_id) {
            $selectedAnnouncement = Announcement::query()
                ->select('id', 'title', 'created_at')
                ->find($editingReminder->announcement_id);

            if ($selectedAnnouncement && ! $announcements->contains('id', $selectedAnnouncement->id)) {
                $announcements->prepend($selectedAnnouncement);
            }
        }

        $reminders = Reminder::query()
            ->with([
                'announcement:id,title',
                'creator:id,name',
                'deactivator:id,name',
            ])
            ->orderByDesc('is_active')
            ->orderBy('remind_at')
            ->paginate(15)
            ->withQueryString();

        return [
            'announcements' => $announcements,
            'reminders' => $reminders,
            'editingReminder' => $editingReminder,
        ];
    }
}
