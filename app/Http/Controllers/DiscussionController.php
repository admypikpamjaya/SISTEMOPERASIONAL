<?php

namespace App\Http\Controllers;

use App\Http\Requests\Discussion\DiscussionMessageStoreRequest;
use App\Models\DiscussionChannel;
use App\Models\DiscussionMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DiscussionController extends Controller
{
    private const DISPLAY_TIMEZONE = 'Asia/Jakarta';

    public function index(Request $request)
    {
        $channels = DiscussionChannel::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        abort_if($channels->isEmpty(), 404, 'Kanal diskusi belum tersedia.');

        $activeChannelId = (int) $request->query('channel', 0);
        $activeChannel = $channels->firstWhere('id', $activeChannelId) ?? $channels->first();
        $this->releaseExpiredPins((int) $activeChannel->id);

        $messages = DiscussionMessage::query()
            ->with(['user:id,name,role', 'pinnedBy:id,name'])
            ->where('channel_id', $activeChannel->id)
            ->orderByDesc('id')
            ->limit(150)
            ->get()
            ->sortBy('id')
            ->values();

        return view('discussion.index', [
            'channels' => $channels,
            'activeChannel' => $activeChannel,
            'initialMessages' => $messages->map(fn (DiscussionMessage $message) => $this->serializeMessage($message))->values()->all(),
            'initialPinnedMessages' => $this->getPinnedMessages((int) $activeChannel->id),
            'currentUserId' => (string) auth()->id(),
        ]);
    }

    public function messages(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'channel_id' => ['required', 'integer', 'exists:discussion_channels,id'],
            'after_id' => ['nullable', 'integer', 'min:0'],
        ]);

        $channelId = (int) $validated['channel_id'];
        $afterId = (int) ($validated['after_id'] ?? 0);
        $this->releaseExpiredPins($channelId);

        $query = DiscussionMessage::query()
            ->with(['user:id,name,role', 'pinnedBy:id,name'])
            ->where('channel_id', $channelId)
            ->orderBy('id');

        if ($afterId > 0) {
            $query->where('id', '>', $afterId);
        }

        $messages = $query
            ->limit(200)
            ->get();

        return response()->json([
            'messages' => $messages->map(fn (DiscussionMessage $message) => $this->serializeMessage($message))->values()->all(),
            'latest_id' => (int) DiscussionMessage::query()
                ->where('channel_id', $channelId)
                ->max('id'),
            'pinned_messages' => $this->getPinnedMessages($channelId),
        ]);
    }

    public function store(DiscussionMessageStoreRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $attachmentPath = null;
        $attachmentName = null;
        $attachmentSize = null;
        $voiceNotePath = null;
        $voiceNoteName = null;
        $voiceNoteSize = null;

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $attachmentPath = $file->store('discussion-files');
            $attachmentName = $file->getClientOriginalName();
            $attachmentSize = $file->getSize();
        }

        if ($request->hasFile('voice_note')) {
            $voiceFile = $request->file('voice_note');
            $voiceNotePath = $voiceFile->store('discussion-voices');
            $voiceNoteName = $voiceFile->getClientOriginalName();
            $voiceNoteSize = $voiceFile->getSize();
        }

        $discussionMessage = DiscussionMessage::query()->create([
            'channel_id' => (int) $validated['channel_id'],
            'user_id' => auth()->id(),
            'message' => $validated['message'] ?? null,
            'attachment_path' => $attachmentPath,
            'attachment_name' => $attachmentName,
            'attachment_size' => $attachmentSize,
            'voice_note_path' => $voiceNotePath,
            'voice_note_name' => $voiceNoteName,
            'voice_note_size' => $voiceNoteSize,
        ]);

        $discussionMessage->load(['user:id,name,role', 'pinnedBy:id,name']);

        return response()->json([
            'message' => 'Pesan berhasil dikirim.',
            'data' => $this->serializeMessage($discussionMessage),
            'pinned_messages' => $this->getPinnedMessages((int) $discussionMessage->channel_id),
        ], 201);
    }

    public function pin(Request $request, DiscussionMessage $message): JsonResponse
    {
        $validated = $request->validate([
            'channel_id' => ['nullable', 'integer', 'exists:discussion_channels,id'],
            'action' => ['nullable', 'in:pin,unpin'],
            'pin_duration' => ['nullable', 'in:24h,48h,1w,1m'],
        ]);

        if (isset($validated['channel_id']) && (int) $validated['channel_id'] !== (int) $message->channel_id) {
            return response()->json([
                'message' => 'Pesan tidak sesuai dengan kanal diskusi yang aktif.',
            ], 422);
        }

        $this->releaseExpiredPins((int) $message->channel_id);
        $action = strtolower((string) ($validated['action'] ?? 'pin'));

        if ($action === 'unpin') {
            $message->update([
                'pinned_at' => null,
                'pin_expires_at' => null,
                'pinned_by' => null,
            ]);

            $statusMessage = 'Pin pesan berhasil dilepas.';
        } else {
            $pinDuration = strtolower((string) ($validated['pin_duration'] ?? ''));
            if (!in_array($pinDuration, ['24h', '48h', '1w', '1m'], true)) {
                return response()->json([
                    'message' => 'Durasi pin wajib dipilih (24 jam, 48 jam, 1 minggu, 1 bulan).',
                ], 422);
            }

            $message->update([
                'pinned_at' => now(),
                'pin_expires_at' => $this->resolvePinExpiry($pinDuration),
                'pinned_by' => auth()->id(),
            ]);

            $statusMessage = 'Pesan berhasil dipin.';
        }

        $message->refresh()->load(['user:id,name,role', 'pinnedBy:id,name']);

        return response()->json([
            'message' => $statusMessage,
            'data' => $this->serializeMessage($message),
            'pinned_messages' => $this->getPinnedMessages((int) $message->channel_id),
        ]);
    }

    public function destroy(Request $request, DiscussionMessage $message): JsonResponse
    {
        $validated = $request->validate([
            'channel_id' => ['nullable', 'integer', 'exists:discussion_channels,id'],
        ]);

        if (isset($validated['channel_id']) && (int) $validated['channel_id'] !== (int) $message->channel_id) {
            return response()->json([
                'message' => 'Pesan tidak sesuai dengan kanal diskusi yang aktif.',
            ], 422);
        }

        if ((string) $message->user_id !== (string) auth()->id()) {
            return response()->json([
                'message' => 'Anda hanya bisa menghapus pesan milik sendiri.',
            ], 403);
        }

        if (!empty($message->attachment_path) && Storage::disk('local')->exists($message->attachment_path)) {
            Storage::disk('local')->delete($message->attachment_path);
        }

        if (!empty($message->voice_note_path) && Storage::disk('local')->exists($message->voice_note_path)) {
            Storage::disk('local')->delete($message->voice_note_path);
        }

        $deletedId = (int) $message->id;
        $channelId = (int) $message->channel_id;
        $message->delete();

        return response()->json([
            'message' => 'Pesan berhasil dihapus.',
            'deleted_id' => $deletedId,
            'pinned_messages' => $this->getPinnedMessages($channelId),
        ]);
    }

    public function attachment(DiscussionMessage $message): StreamedResponse
    {
        abort_if(empty($message->attachment_path) || empty($message->attachment_name), 404);
        abort_unless(Storage::disk('local')->exists($message->attachment_path), 404);

        return Storage::disk('local')->download(
            $message->attachment_path,
            $message->attachment_name
        );
    }

    public function voiceNote(DiscussionMessage $message): StreamedResponse
    {
        abort_if(empty($message->voice_note_path) || empty($message->voice_note_name), 404);
        abort_unless(Storage::disk('local')->exists($message->voice_note_path), 404);

        return Storage::disk('local')->response(
            $message->voice_note_path,
            $message->voice_note_name,
            [
                'Content-Type' => Storage::disk('local')->mimeType($message->voice_note_path) ?: 'audio/webm',
                'Content-Disposition' => 'inline; filename="' . $message->voice_note_name . '"',
            ]
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeMessage(DiscussionMessage $message): array
    {
        $createdAt = $message->created_at?->copy()->timezone(self::DISPLAY_TIMEZONE);
        $pinnedAt = $message->pinned_at?->copy()->timezone(self::DISPLAY_TIMEZONE);
        $pinExpiresAt = $message->pin_expires_at?->copy()->timezone(self::DISPLAY_TIMEZONE);

        return [
            'id' => (int) $message->id,
            'channel_id' => (int) $message->channel_id,
            'message' => $message->message,
            'attachment_url' => $message->attachment_path
                ? route('discussion.messages.attachment', $message)
                : null,
            'attachment_name' => $message->attachment_name,
            'attachment_size' => $message->attachment_size ? (int) $message->attachment_size : null,
            'voice_note_url' => $message->voice_note_path
                ? route('discussion.messages.voice-note', $message)
                : null,
            'voice_note_name' => $message->voice_note_name,
            'voice_note_size' => $message->voice_note_size ? (int) $message->voice_note_size : null,
            'is_pinned' => !empty($message->pinned_at),
            'pinned_by_id' => $message->pinnedBy?->id ? (string) $message->pinnedBy->id : null,
            'pinned_by_name' => $message->pinnedBy?->name,
            'pinned_at' => $pinnedAt?->toIso8601String(),
            'pinned_at_label' => $pinnedAt?->format('d M Y H:i'),
            'pin_expires_at' => $pinExpiresAt?->toIso8601String(),
            'pin_expires_at_label' => $pinExpiresAt?->format('d M Y H:i'),
            'is_mine' => (string) $message->user_id === (string) auth()->id(),
            'sender' => [
                'id' => $message->user?->id ? (string) $message->user->id : null,
                'name' => $message->user?->name ?? 'Pengguna',
                'role' => $message->user?->role ?? '-',
            ],
            'created_at' => $createdAt?->toIso8601String(),
            'created_at_label' => $createdAt?->format('d M Y H:i') ?? '-',
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getPinnedMessages(int $channelId): array
    {
        $this->releaseExpiredPins($channelId);

        $pinnedMessages = DiscussionMessage::query()
            ->with(['user:id,name,role', 'pinnedBy:id,name'])
            ->where('channel_id', $channelId)
            ->whereNotNull('pinned_at')
            ->where(function ($query) {
                $query->whereNull('pin_expires_at')
                    ->orWhere('pin_expires_at', '>', now());
            })
            ->orderByDesc('pinned_at')
            ->limit(8)
            ->get();

        return $pinnedMessages
            ->map(fn (DiscussionMessage $message) => $this->serializeMessage($message))
            ->values()
            ->all();
    }

    private function releaseExpiredPins(int $channelId): void
    {
        DiscussionMessage::query()
            ->where('channel_id', $channelId)
            ->whereNotNull('pinned_at')
            ->whereNotNull('pin_expires_at')
            ->where('pin_expires_at', '<=', now())
            ->update([
                'pinned_at' => null,
                'pin_expires_at' => null,
                'pinned_by' => null,
            ]);
    }

    private function resolvePinExpiry(string $pinDuration): \Illuminate\Support\Carbon
    {
        return match ($pinDuration) {
            '24h' => now()->addHours(24),
            '48h' => now()->addHours(48),
            '1w' => now()->addWeek(),
            '1m' => now()->addMonth(),
            default => now()->addHours(24),
        };
    }
}
