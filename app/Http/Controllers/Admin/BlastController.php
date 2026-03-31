<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\DataTransferObjects\BlastPayload;
use App\DataTransferObjects\BlastAttachment;
use App\Jobs\Blast\QueueBlastDeliveryJob;
use App\Models\BlastEmployeeRecipient;
use App\Models\BlastEmployeeYpikRecipient;
use App\Models\BlastRecipient;
use App\Models\BlastMessageTemplate;
use App\Models\BlastMessage;
use App\Models\BlastTarget;
use App\Models\BlastLog;
use App\Models\Announcement;
use App\Services\Blast\TemplateRenderer;
use App\Services\Blast\RecipientSelectorService;
use App\Services\Blast\TunggakanMessageContextService;
use App\Services\Blast\WhatsAppGatewayDeviceService;
use App\Services\Blast\WhatsAppProviderSelector;
use App\Services\Blast\WhatsAppDeviceLabelStore;
use App\Enums\User\UserRole;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class BlastController extends Controller
{
    private const WIB_TIMEZONE = 'Asia/Jakarta';

    /* =======================
     |  VIEW
     ======================= */

    public function index()
    {
        return view('admin.blast.index');
    }

    public function whatsapp(
        WhatsAppProviderSelector $providerSelector,
        WhatsAppDeviceLabelStore $labelStore
    )
    {
        session()->forget('campaign_id');

        $recipients = $this->getRecipientsByChannel('whatsapp');
        $recipientClasses = $recipients
            ->pluck('kelas')
            ->map(fn ($kelas) => trim((string) $kelas))
            ->filter(fn (string $kelas) => $kelas !== '')
            ->unique()
            ->sort()
            ->values();

        $templates = BlastMessageTemplate::whereIn('channel', ['whatsapp', 'WHATSAPP'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $announcementOptions = Announcement::query()
            ->latest('id')
            ->limit(100)
            ->get(['id', 'title', 'message']);

        $activityData = $this->buildChannelActivityData(
            'WHATSAPP',
            $recipients,
            $labelStore->getLabels()
        );
        $activityLogs = $activityData['logs'];
        $activityStats = $activityData['stats'];
        $rawApiKey = (string) config('services.whatsapp_gateway.api_key', '');
        $gatewayConfig = [
            'base_url' => (string) config('services.whatsapp_gateway.base_url', ''),
            'api_key_header' => (string) config('services.whatsapp_gateway.api_key_header', 'X-API-KEY'),
            'api_key_display' => trim($rawApiKey) !== '' ? $rawApiKey : '-',
            'api_key_masked' => $this->maskGatewayApiKey($rawApiKey),
        ];

        $providerState = [
            'current' => $providerSelector->getProvider(),
            'allowed' => ['gateway', 'wablas'],
        ];

        return view('admin.blast.whatsapp', compact(
            'recipients',
            'recipientClasses',
            'templates',
            'announcementOptions',
            'activityLogs',
            'activityStats',
            'gatewayConfig',
            'providerState'
        ));
    }

    public function whatsappManagePhone(WhatsAppProviderSelector $providerSelector)
    {
        $user = Auth::user();
        if (!$user || $user->role !== UserRole::IT_SUPPORT->value) {
            abort(403);
        }

        $rawApiKey = (string) config('services.whatsapp_gateway.api_key', '');
        $gatewayConfig = [
            'base_url' => (string) config('services.whatsapp_gateway.base_url', ''),
            'api_key_header' => (string) config('services.whatsapp_gateway.api_key_header', 'X-API-KEY'),
            'api_key_display' => trim($rawApiKey) !== '' ? $rawApiKey : '-',
            'api_key_masked' => $this->maskGatewayApiKey($rawApiKey),
        ];

        $providerState = [
            'current' => $providerSelector->getProvider(),
            'allowed' => ['gateway', 'wablas'],
        ];

        return view('admin.blast.whatsapp-manage', compact('gatewayConfig', 'providerState'));
    }

    public function whatsappProviderStatus(WhatsAppProviderSelector $providerSelector)
    {
        $user = Auth::user();
        if (!$user || $user->role !== UserRole::IT_SUPPORT->value) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden',
                'data' => [],
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'OK',
            'data' => [
                'provider' => $providerSelector->getProvider(),
            ],
        ]);
    }

    public function whatsappProviderUpdate(
        Request $request,
        WhatsAppProviderSelector $providerSelector
    ) {
        $user = Auth::user();
        if (!$user || $user->role !== UserRole::IT_SUPPORT->value) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden',
                'data' => [],
            ], 403);
        }

        $provider = strtolower(trim((string) $request->input('provider', '')));
        $allowed = ['gateway', 'wablas'];
        if (!in_array($provider, $allowed, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Provider tidak valid.',
                'data' => [
                    'allowed' => $allowed,
                ],
            ], 422);
        }

        $providerSelector->setProvider($provider);

        return response()->json([
            'success' => true,
            'message' => 'Provider diperbarui.',
            'data' => [
                'provider' => $provider,
            ],
        ]);
    }

    public function whatsappGatewayStatus()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden',
                'data' => [],
            ], 403);
        }

        $isSuperAdmin = $user->role === UserRole::IT_SUPPORT->value;

        try {
            [$baseUrl, $client] = $this->buildGatewayClient();
            $response = $client->get($baseUrl . '/status');
        } catch (\Throwable $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage() ?: 'Gateway tidak dapat dihubungi.',
                'data' => [],
            ], 502);
        }

        if (!$response->successful()) {
            return response()->json([
                'success' => false,
                'message' => 'Gateway merespon error.',
                'data' => [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ],
            ], 502);
        }

        $payload = $response->json();

        $data = $payload['data'] ?? $payload;
        if (!$isSuperAdmin) {
            $data = [
                'status' => $data['status'] ?? 'disconnected',
                'activeDeviceId' => $data['activeDeviceId'] ?? null,
            ];
        }

        return response()->json([
            'success' => (bool) ($payload['success'] ?? true),
            'message' => (string) ($payload['message'] ?? 'OK'),
            'data' => $data,
        ]);
    }

    public function whatsappGatewayReconnect()
    {
        $user = Auth::user();
        if (!$user || $user->role !== UserRole::IT_SUPPORT->value) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden',
                'data' => [],
            ], 403);
        }

        try {
            [$baseUrl, $client] = $this->buildGatewayClient();
            $response = $client->post($baseUrl . '/reconnect');
        } catch (\Throwable $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage() ?: 'Gateway tidak dapat dihubungi.',
                'data' => [],
            ], 502);
        }

        if (!$response->successful()) {
            return response()->json([
                'success' => false,
                'message' => 'Gateway merespon error.',
                'data' => [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ],
            ], 502);
        }

        $payload = $response->json();

        return response()->json([
            'success' => (bool) ($payload['success'] ?? true),
            'message' => (string) ($payload['message'] ?? 'Reconnect requested'),
            'data' => $payload['data'] ?? $payload,
        ]);
    }

    public function whatsappGatewayDevices(WhatsAppGatewayDeviceService $deviceService)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden',
                'data' => [],
            ], 403);
        }

        $isSuperAdmin = $user->role === UserRole::IT_SUPPORT->value;

        try {
            $payload = $deviceService->listDevices($isSuperAdmin);
        } catch (\Throwable $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage() ?: 'Gateway tidak dapat dihubungi.',
                'data' => [],
            ], 502);
        }

        return response()->json([
            'success' => (bool) ($payload['success'] ?? true),
            'message' => (string) ($payload['message'] ?? 'OK'),
            'data' => $payload['data'] ?? [],
        ]);
    }

    public function whatsappGatewayDeviceCreate(Request $request)
    {
        $user = Auth::user();
        if (!$user || $user->role !== UserRole::IT_SUPPORT->value) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden',
                'data' => [],
            ], 403);
        }

        $deviceId = $this->sanitizeDeviceId($request->input('device_id'));
        if ($deviceId === null) {
            return response()->json([
                'success' => false,
                'message' => 'Device ID tidak valid.',
                'data' => [],
            ], 422);
        }

        try {
            [$baseUrl, $client] = $this->buildGatewayClient();
            $response = $client->post($baseUrl . '/devices', [
                'deviceId' => $deviceId,
            ]);
        } catch (\Throwable $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage() ?: 'Gateway tidak dapat dihubungi.',
                'data' => [],
            ], 502);
        }

        if (!$response->successful()) {
            return response()->json([
                'success' => false,
                'message' => 'Gateway merespon error.',
                'data' => [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ],
            ], 502);
        }

        $payload = $response->json();

        return response()->json([
            'success' => (bool) ($payload['success'] ?? true),
            'message' => (string) ($payload['message'] ?? 'OK'),
            'data' => $payload['data'] ?? $payload,
        ]);
    }

    public function whatsappGatewayDeviceConnect(string $deviceId)
    {
        $user = Auth::user();
        if (!$user || $user->role !== UserRole::IT_SUPPORT->value) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden',
                'data' => [],
            ], 403);
        }

        $deviceId = $this->sanitizeDeviceId($deviceId);
        if ($deviceId === null) {
            return response()->json([
                'success' => false,
                'message' => 'Device ID tidak valid.',
                'data' => [],
            ], 422);
        }

        try {
            [$baseUrl, $client] = $this->buildGatewayClient();
            $response = $client->post($baseUrl . '/devices/' . $deviceId . '/connect');
        } catch (\Throwable $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage() ?: 'Gateway tidak dapat dihubungi.',
                'data' => [],
            ], 502);
        }

        if (!$response->successful()) {
            return response()->json([
                'success' => false,
                'message' => 'Gateway merespon error.',
                'data' => [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ],
            ], 502);
        }

        $payload = $response->json();

        return response()->json([
            'success' => (bool) ($payload['success'] ?? true),
            'message' => (string) ($payload['message'] ?? 'OK'),
            'data' => $payload['data'] ?? $payload,
        ]);
    }

    public function whatsappGatewayDeviceActivate(string $deviceId)
    {
        $user = Auth::user();
        if (!$user || $user->role !== UserRole::IT_SUPPORT->value) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden',
                'data' => [],
            ], 403);
        }

        $deviceId = $this->sanitizeDeviceId($deviceId);
        if ($deviceId === null) {
            return response()->json([
                'success' => false,
                'message' => 'Device ID tidak valid.',
                'data' => [],
            ], 422);
        }

        try {
            [$baseUrl, $client] = $this->buildGatewayClient();
            $response = $client->post($baseUrl . '/devices/' . $deviceId . '/activate');
        } catch (\Throwable $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage() ?: 'Gateway tidak dapat dihubungi.',
                'data' => [],
            ], 502);
        }

        if (!$response->successful()) {
            return response()->json([
                'success' => false,
                'message' => 'Gateway merespon error.',
                'data' => [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ],
            ], 502);
        }

        $payload = $response->json();

        return response()->json([
            'success' => (bool) ($payload['success'] ?? true),
            'message' => (string) ($payload['message'] ?? 'OK'),
            'data' => $payload['data'] ?? $payload,
        ]);
    }

    public function whatsappGatewayDeviceReconnect(string $deviceId)
    {
        $user = Auth::user();
        if (!$user || $user->role !== UserRole::IT_SUPPORT->value) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden',
                'data' => [],
            ], 403);
        }

        $deviceId = $this->sanitizeDeviceId($deviceId);
        if ($deviceId === null) {
            return response()->json([
                'success' => false,
                'message' => 'Device ID tidak valid.',
                'data' => [],
            ], 422);
        }

        try {
            [$baseUrl, $client] = $this->buildGatewayClient();
            $response = $client->post($baseUrl . '/devices/' . $deviceId . '/reconnect');
        } catch (\Throwable $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage() ?: 'Gateway tidak dapat dihubungi.',
                'data' => [],
            ], 502);
        }

        if (!$response->successful()) {
            return response()->json([
                'success' => false,
                'message' => 'Gateway merespon error.',
                'data' => [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ],
            ], 502);
        }

        $payload = $response->json();

        return response()->json([
            'success' => (bool) ($payload['success'] ?? true),
            'message' => (string) ($payload['message'] ?? 'OK'),
            'data' => $payload['data'] ?? $payload,
        ]);
    }

    public function whatsappGatewayDeviceDisconnect(string $deviceId)
    {
        $user = Auth::user();
        if (!$user || $user->role !== UserRole::IT_SUPPORT->value) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden',
                'data' => [],
            ], 403);
        }

        $deviceId = $this->sanitizeDeviceId($deviceId);
        if ($deviceId === null) {
            return response()->json([
                'success' => false,
                'message' => 'Device ID tidak valid.',
                'data' => [],
            ], 422);
        }

        try {
            [$baseUrl, $client] = $this->buildGatewayClient();
            $response = $client->post($baseUrl . '/devices/' . $deviceId . '/disconnect');
        } catch (\Throwable $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage() ?: 'Gateway tidak dapat dihubungi.',
                'data' => [],
            ], 502);
        }

        if (!$response->successful()) {
            return response()->json([
                'success' => false,
                'message' => 'Gateway merespon error.',
                'data' => [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ],
            ], 502);
        }

        $payload = $response->json();

        return response()->json([
            'success' => (bool) ($payload['success'] ?? true),
            'message' => (string) ($payload['message'] ?? 'OK'),
            'data' => $payload['data'] ?? $payload,
        ]);
    }

    public function whatsappGatewayDeviceDelete(
        string $deviceId,
        WhatsAppDeviceLabelStore $labelStore
    )
    {
        $user = Auth::user();
        if (!$user || $user->role !== UserRole::IT_SUPPORT->value) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden',
                'data' => [],
            ], 403);
        }

        $deviceId = $this->sanitizeDeviceId($deviceId);
        if ($deviceId === null) {
            return response()->json([
                'success' => false,
                'message' => 'Device ID tidak valid.',
                'data' => [],
            ], 422);
        }

        try {
            [$baseUrl, $client] = $this->buildGatewayClient();
            $response = $client->delete($baseUrl . '/devices/' . $deviceId);
        } catch (\Throwable $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage() ?: 'Gateway tidak dapat dihubungi.',
                'data' => [],
            ], 502);
        }

        if (!$response->successful()) {
            return response()->json([
                'success' => false,
                'message' => 'Gateway merespon error.',
                'data' => [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ],
            ], 502);
        }

        $payload = $response->json();

        $labelStore->removeLabel($deviceId);

        return response()->json([
            'success' => (bool) ($payload['success'] ?? true),
            'message' => (string) ($payload['message'] ?? 'OK'),
            'data' => $payload['data'] ?? $payload,
        ]);
    }

    public function whatsappGatewayDeviceRename(
        string $deviceId,
        Request $request,
        WhatsAppDeviceLabelStore $labelStore
    ) {
        $user = Auth::user();
        if (!$user || $user->role !== UserRole::IT_SUPPORT->value) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden',
                'data' => [],
            ], 403);
        }

        $deviceId = $this->sanitizeDeviceId($deviceId);
        if ($deviceId === null) {
            return response()->json([
                'success' => false,
                'message' => 'Device ID tidak valid.',
                'data' => [],
            ], 422);
        }

        $label = trim((string) $request->input('label', ''));
        if ($label === '') {
            return response()->json([
                'success' => false,
                'message' => 'Label tidak boleh kosong.',
                'data' => [],
            ], 422);
        }

        $labelStore->setLabel($deviceId, $label);

        return response()->json([
            'success' => true,
            'message' => 'Label device diperbarui.',
            'data' => [
                'deviceId' => $deviceId,
                'label' => $label,
            ],
        ]);
    }

    public function whatsappGatewayDevicesReset(
        WhatsAppDeviceLabelStore $labelStore
    ) {
        $user = Auth::user();
        if (!$user || $user->role !== UserRole::IT_SUPPORT->value) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden',
                'data' => [],
            ], 403);
        }

        try {
            [$baseUrl, $client] = $this->buildGatewayClient();
            $response = $client->post($baseUrl . '/devices/reset');
        } catch (\Throwable $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage() ?: 'Gateway tidak dapat dihubungi.',
                'data' => [],
            ], 502);
        }

        if (!$response->successful()) {
            return response()->json([
                'success' => false,
                'message' => 'Gateway merespon error.',
                'data' => [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ],
            ], 502);
        }

        $payload = $response->json();
        $labelStore->clearLabels();

        return response()->json([
            'success' => (bool) ($payload['success'] ?? true),
            'message' => (string) ($payload['message'] ?? 'Devices reset.'),
            'data' => $payload['data'] ?? $payload,
        ]);
    }

    public function email()
    {
        session()->forget('campaign_id');

        $recipients = $this->getRecipientsByChannel('email');
        $recipientClasses = $recipients
            ->pluck('kelas')
            ->map(fn ($kelas) => trim((string) $kelas))
            ->filter(fn (string $kelas) => $kelas !== '')
            ->unique()
            ->sort()
            ->values();

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
            compact('recipients', 'recipientClasses', 'templates', 'announcementOptions', 'activityLogs', 'activityStats')
        );
    }

    public function activity(
        Request $request,
        WhatsAppDeviceLabelStore $labelStore
    )
    {
        $validated = $request->validate([
            'channel' => 'required|in:email,whatsapp',
        ]);

        $channel = strtolower((string) $validated['channel']);
        $recipients = $this->getRecipientsByChannel($channel);
        $deviceLabels = [];
        if ($channel === 'whatsapp') {
            $deviceLabels = $labelStore->getLabels();
        }
        $activityData = $this->buildChannelActivityData(
            strtoupper($channel),
            $recipients,
            $deviceLabels
        );

        return response()->json($activityData);
    }

    public function clearActivityLogs(Request $request)
    {
        $validated = $request->validate([
            'channel' => 'required|in:email,whatsapp',
        ]);

        $channel = strtoupper((string) $validated['channel']);
        $deletedLogCount = BlastLog::query()
            ->whereHas('message', function ($query) use ($channel) {
                $query->where('channel', $channel);
            })
            ->delete();

        $channelLabel = $channel === 'WHATSAPP' ? 'WhatsApp' : 'Email';

        return back()->with(
            'success',
            'Activity log ' . $channelLabel . ' berhasil dibersihkan. '
            . 'Data terhapus: ' . $deletedLogCount . '.'
        );
    }

    public function deleteActivityLog(Request $request)
    {
        $validated = $request->validate([
            'channel' => 'required|in:email,whatsapp',
            'log_id' => 'required|integer|exists:blast_logs,id',
        ]);

        $channel = strtoupper((string) $validated['channel']);
        $channelLabel = $channel === 'WHATSAPP' ? 'WhatsApp' : 'Email';

        $blastLog = BlastLog::query()
            ->with(['message:id,channel', 'target:id'])
            ->whereKey((int) $validated['log_id'])
            ->whereHas('message', function ($query) use ($channel) {
                $query->where('channel', $channel);
            })
            ->first();

        if ($blastLog === null) {
            return $this->activityActionError(
                $request,
                'Activity log ' . $channelLabel . ' tidak ditemukan.',
                404
            );
        }

        $blastTarget = $blastLog->target;
        $blastLog->delete();

        if (
            $blastTarget !== null
            && !BlastLog::query()->where('blast_target_id', $blastTarget->id)->exists()
        ) {
            $blastTarget->delete();
        }

        return $this->activityActionSuccess(
            $request,
            'Activity log ' . $channelLabel . ' berhasil dihapus.'
        );
    }

    public function retryActivityLog(Request $request)
    {
        $validated = $request->validate([
            'channel' => 'required|in:email,whatsapp',
            'log_id' => 'required|integer|exists:blast_logs,id',
        ]);

        $requestedChannel = strtoupper((string) $validated['channel']);

        $blastLog = BlastLog::query()
            ->with([
                'message:id,channel,subject,message,meta,priority,campaign_status',
                'target:id,target',
            ])
            ->whereKey((int) $validated['log_id'])
            ->whereHas('message', function ($query) use ($requestedChannel) {
                $query->where('channel', $requestedChannel);
            })
            ->first();

        if ($blastLog === null || $blastLog->message === null) {
            return $this->activityActionError(
                $request,
                'Activity log tidak ditemukan atau campaign sudah tidak tersedia.',
                404
            );
        }

        if (strtoupper((string) $blastLog->status) !== 'FAILED') {
            return $this->activityActionError(
                $request,
                'Hanya activity log dengan status gagal yang bisa di-retry.',
                422
            );
        }

        if (strtoupper((string) $blastLog->message->campaign_status) === 'STOPPED') {
            return $this->activityActionError(
                $request,
                'Campaign masih berstatus STOPPED. Resume campaign terlebih dahulu.',
                422
            );
        }

        $channel = strtoupper((string) $blastLog->message->channel);
        $channelLabel = $channel === 'WHATSAPP' ? 'WhatsApp' : 'Email';

        $target = trim((string) optional($blastLog->target)->target);
        if ($target === '') {
            return $this->activityActionError(
                $request,
                'Target penerima untuk retry tidak ditemukan.',
                422
            );
        }

        $messageSnapshot = trim((string) $blastLog->message_snapshot);
        if ($messageSnapshot === '') {
            $messageSnapshot = trim((string) $blastLog->message->message);
        }

        if ($messageSnapshot === '') {
            return $this->activityActionError(
                $request,
                'Isi pesan snapshot tidak tersedia untuk proses retry.',
                422
            );
        }

        $campaignMeta = $this->extractCampaignMeta($blastLog->message->meta);
        $priority = strtolower((string) ($blastLog->message->priority ?? 'normal'));
        if (!in_array($priority, ['high', 'normal', 'low'], true)) {
            $priority = 'normal';
        }

        $queueName = trim((string) (
            $campaignMeta['queue_name']
            ?? $this->resolveQueueName(strtolower($channel), $priority)
        ));

        $retryAttempts = max(
            1,
            (int) ($campaignMeta['retry_attempts'] ?? config('blast.retry.max_attempts', 3))
        );
        if ($channel === 'WHATSAPP') {
            $retryAttempts = 1;
        }
        $retryBackoffSeconds = $this->normalizeRetryBackoffSeconds(
            $campaignMeta['retry_backoff_seconds'] ?? null
        );

        $payload = new BlastPayload($messageSnapshot);
        $payload->setMeta('channel', $channel);
        $payload->setMeta('sent_by', Auth::id());
        $payload->setMeta('blast_log_id', $blastLog->id);
        $payload->setMeta('blast_message_id', $blastLog->blast_message_id);
        $payload->setMeta('queue_name', $queueName);
        $payload->setMeta('retry_attempts', $retryAttempts);
        $payload->setMeta('retry_backoff_seconds', $retryBackoffSeconds);

        $subject = $channel === 'EMAIL'
            ? trim((string) $blastLog->message->subject)
            : null;

        $blastLog->update([
            'status' => 'PENDING',
            'response' => 'Retry requested by operator.',
            'error_message' => null,
            'sent_at' => null,
            'attempt' => 0,
        ]);

        $job = new QueueBlastDeliveryJob(
            $channel,
            $target,
            $subject,
            $payload
        );
        app(\Illuminate\Contracts\Bus\Dispatcher::class)->dispatchSync($job);

        return $this->activityActionSuccess(
            $request,
            'Retry blast ' . $channelLabel . ' diproses.'
        );
    }

    /* =======================
     |  WHATSAPP BLAST
     ======================= */

    public function sendWhatsapp(
        Request $request,
        TemplateRenderer $renderer,
        TunggakanMessageContextService $tunggakanContextService
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

            'device_student' => 'nullable|string',
            'device_employee' => 'nullable|string',
            'device_manual' => 'nullable|string',
        ]);

        $deviceStudent = $this->sanitizeDeviceId($validated['device_student'] ?? null);
        $deviceEmployee = $this->sanitizeDeviceId($validated['device_employee'] ?? null);
        $deviceManual = $this->sanitizeDeviceId($validated['device_manual'] ?? null);
        if ($deviceManual === null) {
            $deviceManual = $deviceStudent;
        }

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
            $recipientIds = array_values(
                array_unique(
                    array_map(
                        fn ($value) => trim((string) $value),
                        (array) $validated['recipient_ids']
                    )
                )
            );

            $studentRecipients = BlastRecipient::whereIn('id', $recipientIds)
                ->where(function ($query) {
                    $query->whereNotNull('wa_wali')
                        ->orWhereNotNull('wa_wali_2');
                })
                ->where('is_valid', true)
                ->get();

            foreach ($studentRecipients as $recipient) {
                $targets = $this->collectWhatsappTargets($recipient);
                if (empty($targets)) {
                    continue;
                }

                $message = $this->resolveDbRecipientWhatsappMessage(
                    recipient: $recipient,
                    renderer: $renderer,
                    template: $template,
                    globalMessage: $validated['message'] ?? '',
                    useGlobalDefault: $useGlobalDefault,
                    messageOverrides: $messageOverrides,
                    tunggakanContextService: $tunggakanContextService
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

                foreach ($targets as $target) {
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
                    $this->applyDeviceToPayload($payload, $deviceStudent);
                    $this->attachWhatsappFilesToPayload(
                        $payload,
                        $attachments,
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

            $employeeRecipients = BlastEmployeeRecipient::query()
                ->whereIn('id', $recipientIds)
                ->whereNotNull('wa_karyawan')
                ->where('is_valid', true)
                ->get();

            foreach ($employeeRecipients as $employee) {
                $recipient = $this->toPseudoRecipientFromEmployee($employee);
                $targets = $this->collectWhatsappTargets($recipient);
                if (empty($targets)) {
                    continue;
                }

                $message = $this->resolveDbRecipientWhatsappMessage(
                    recipient: $recipient,
                    renderer: $renderer,
                    template: $template,
                    globalMessage: $validated['message'] ?? '',
                    useGlobalDefault: $useGlobalDefault,
                    messageOverrides: $messageOverrides,
                    tunggakanContextService: $tunggakanContextService
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

                foreach ($targets as $target) {
                    $blastLog = $this->createBlastLogRecord(
                        blastMessage: $blastMessage,
                        target: $target,
                        messageSnapshot: $message
                    );

                    $payload = new BlastPayload($message);
                    $payload->setMeta('channel', 'WHATSAPP');
                    $payload->setMeta('sent_by', Auth::id());
                    $payload->setMeta('recipient_id', $employee->id);
                    $payload->setMeta('recipient_source', 'karyawan');
                    $payload->setMeta('blast_log_id', $blastLog->id);
                    $payload->setMeta('blast_message_id', $blastMessage->id);
                    $payload->setMeta('queue_name', $campaignOptions['queue_name']);
                    $payload->setMeta('retry_attempts', $campaignOptions['retry_attempts']);
                    $payload->setMeta('retry_backoff_seconds', $campaignOptions['retry_backoff_seconds']);
                    $this->applyDeviceToPayload($payload, $deviceEmployee);
                    $this->attachWhatsappFilesToPayload(
                        $payload,
                        $attachments,
                        $recipientAttachmentOverrides['db:' . $employee->id] ?? []
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

            $employeeYpikRecipients = BlastEmployeeYpikRecipient::query()
                ->whereIn('id', $recipientIds)
                ->whereNotNull('wa_karyawan')
                ->where('is_valid', true)
                ->get();

            foreach ($employeeYpikRecipients as $employeeYpik) {
                $recipient = $this->toPseudoRecipientFromEmployeeYpik($employeeYpik);
                $targets = $this->collectWhatsappTargets($recipient);
                if (empty($targets)) {
                    continue;
                }

                $message = $this->resolveDbRecipientWhatsappMessage(
                    recipient: $recipient,
                    renderer: $renderer,
                    template: $template,
                    globalMessage: $validated['message'] ?? '',
                    useGlobalDefault: $useGlobalDefault,
                    messageOverrides: $messageOverrides,
                    tunggakanContextService: $tunggakanContextService
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

                foreach ($targets as $target) {
                    $blastLog = $this->createBlastLogRecord(
                        blastMessage: $blastMessage,
                        target: $target,
                        messageSnapshot: $message
                    );

                    $payload = new BlastPayload($message);
                    $payload->setMeta('channel', 'WHATSAPP');
                    $payload->setMeta('sent_by', Auth::id());
                    $payload->setMeta('recipient_id', $employeeYpik->id);
                    $payload->setMeta('recipient_source', 'karyawan_ypik');
                    $payload->setMeta('blast_log_id', $blastLog->id);
                    $payload->setMeta('blast_message_id', $blastMessage->id);
                    $payload->setMeta('queue_name', $campaignOptions['queue_name']);
                    $payload->setMeta('retry_attempts', $campaignOptions['retry_attempts']);
                    $payload->setMeta('retry_backoff_seconds', $campaignOptions['retry_backoff_seconds']);
                    $this->applyDeviceToPayload($payload, $deviceEmployee);
                    $this->attachWhatsappFilesToPayload(
                        $payload,
                        $attachments,
                        $recipientAttachmentOverrides['db:' . $employeeYpik->id] ?? []
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
            $this->applyDeviceToPayload($payload, $deviceManual);
            $this->attachWhatsappFilesToPayload(
                $payload,
                $attachments,
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
        $statusMessage = 'WhatsApp blast diproses.';
        if ($campaignId === null) {
            $statusMessage = 'Tidak ada target WhatsApp valid untuk diproses.';
        }

        return back()->with('success', $statusMessage);
    }

    /* =======================
     |  EMAIL
     ======================= */

    public function sendEmail(
        Request $request,
        TemplateRenderer $renderer,
        TunggakanMessageContextService $tunggakanContextService
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
            $recipientIds = array_values(
                array_unique(
                    array_map(
                        fn ($value) => trim((string) $value),
                        (array) $validated['recipient_ids']
                    )
                )
            );

            $recipients = BlastRecipient::whereIn('id', $recipientIds)
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
                    messageOverrides: $messageOverrides,
                    tunggakanContextService: $tunggakanContextService
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

            $employeeRecipients = BlastEmployeeRecipient::query()
                ->whereIn('id', $recipientIds)
                ->whereNotNull('email_karyawan')
                ->where('is_valid', true)
                ->get()
                ->filter(
                    fn (BlastEmployeeRecipient $employee) =>
                        filter_var($employee->email_karyawan, FILTER_VALIDATE_EMAIL)
                )
                ->values();

            foreach ($employeeRecipients as $employee) {
                $recipient = $this->toPseudoRecipientFromEmployee($employee);
                $message = $this->resolveDbRecipientEmailMessage(
                    recipient: $recipient,
                    renderer: $renderer,
                    template: $template,
                    globalMessage: $validated['message'] ?? '',
                    useGlobalDefault: $useGlobalDefault,
                    messageOverrides: $messageOverrides,
                    tunggakanContextService: $tunggakanContextService
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
                    target: (string) $employee->email_karyawan,
                    messageSnapshot: $message
                );

                $payload = new BlastPayload($message);
                $payload->setMeta('channel', 'EMAIL');
                $payload->setMeta('sent_by', Auth::id());
                $payload->setMeta('recipient_id', $employee->id);
                $payload->setMeta('recipient_source', 'karyawan');
                $payload->setMeta('blast_log_id', $blastLog->id);
                $payload->setMeta('blast_message_id', $blastMessage->id);
                $payload->setMeta('queue_name', $campaignOptions['queue_name']);
                $payload->setMeta('retry_attempts', $campaignOptions['retry_attempts']);
                $payload->setMeta('retry_backoff_seconds', $campaignOptions['retry_backoff_seconds']);
                $this->attachFilesToPayload($payload, $attachments);
                $this->attachFilesToPayload(
                    $payload,
                    $recipientAttachmentOverrides['db:' . $employee->id] ?? []
                );

                $this->dispatchQueuedBlastDelivery(
                    channel: 'EMAIL',
                    target: (string) $employee->email_karyawan,
                    subject: $validated['subject'],
                    payload: $payload,
                    campaignOptions: $campaignOptions,
                    dispatchIndex: $dispatchIndex
                );
            }

            $employeeYpikRecipients = BlastEmployeeYpikRecipient::query()
                ->whereIn('id', $recipientIds)
                ->whereNotNull('email_karyawan')
                ->where('is_valid', true)
                ->get()
                ->filter(
                    fn (BlastEmployeeYpikRecipient $employeeYpik) =>
                        filter_var($employeeYpik->email_karyawan, FILTER_VALIDATE_EMAIL)
                )
                ->values();

            foreach ($employeeYpikRecipients as $employeeYpik) {
                $recipient = $this->toPseudoRecipientFromEmployeeYpik($employeeYpik);
                $message = $this->resolveDbRecipientEmailMessage(
                    recipient: $recipient,
                    renderer: $renderer,
                    template: $template,
                    globalMessage: $validated['message'] ?? '',
                    useGlobalDefault: $useGlobalDefault,
                    messageOverrides: $messageOverrides,
                    tunggakanContextService: $tunggakanContextService
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
                    target: (string) $employeeYpik->email_karyawan,
                    messageSnapshot: $message
                );

                $payload = new BlastPayload($message);
                $payload->setMeta('channel', 'EMAIL');
                $payload->setMeta('sent_by', Auth::id());
                $payload->setMeta('recipient_id', $employeeYpik->id);
                $payload->setMeta('recipient_source', 'karyawan_ypik');
                $payload->setMeta('blast_log_id', $blastLog->id);
                $payload->setMeta('blast_message_id', $blastMessage->id);
                $payload->setMeta('queue_name', $campaignOptions['queue_name']);
                $payload->setMeta('retry_attempts', $campaignOptions['retry_attempts']);
                $payload->setMeta('retry_backoff_seconds', $campaignOptions['retry_backoff_seconds']);
                $this->attachFilesToPayload($payload, $attachments);
                $this->attachFilesToPayload(
                    $payload,
                    $recipientAttachmentOverrides['db:' . $employeeYpik->id] ?? []
                );

                $this->dispatchQueuedBlastDelivery(
                    channel: 'EMAIL',
                    target: (string) $employeeYpik->email_karyawan,
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

        $statusMessage = 'Email blast diproses.';
        if ($blastMessage === null) {
            $statusMessage = 'Tidak ada target email valid untuk diproses.';
        }

        return back()->with('success', $statusMessage);
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
                $scheduledAt = $campaign->scheduled_at?->copy()->timezone(self::WIB_TIMEZONE);
                $createdAt = $campaign->created_at?->copy()->timezone(self::WIB_TIMEZONE);

                return [
                    'id' => $campaign->id,
                    'channel' => strtoupper((string) $campaign->channel),
                    'status' => strtoupper((string) $campaign->campaign_status),
                    'priority' => strtolower((string) $campaign->priority),
                    'scheduledAt' => $scheduledAt?->format('Y-m-d H:i:s'),
                    'createdAt' => $createdAt?->format('Y-m-d H:i:s'),
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
            'paused_at' => now(self::WIB_TIMEZONE),
            'completed_at' => null,
        ]);

        return back()
            ->with('success', 'Campaign paused.');
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
            'started_at' => $campaign->started_at ?? now(self::WIB_TIMEZONE),
        ]);

        return back()
            ->with('success', 'Campaign resumed.');
    }

    public function stopCampaign(Request $request)
    {
        $validated = $request->validate([
            'campaign_id' => 'required|string|exists:blast_messages,id',
        ]);

        $campaign = BlastMessage::query()->findOrFail($validated['campaign_id']);
        $campaign->update([
            'campaign_status' => 'STOPPED',
            'completed_at' => now(self::WIB_TIMEZONE),
            'paused_at' => null,
        ]);

        BlastLog::query()
            ->where('blast_message_id', $campaign->id)
            ->where('status', 'PENDING')
            ->update([
                'status' => 'FAILED',
                'error_message' => 'Campaign stopped by operator.',
                'sent_at' => now(self::WIB_TIMEZONE),
            ]);

        return back()
            ->with('success', 'Campaign stopped.');
    }

    private function activityActionSuccess(
        Request $request,
        string $message
    ) {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }

    private function activityActionError(
        Request $request,
        string $message,
        int $status = 422
    ) {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => $message,
            ], $status);
        }

        return back()->with('error', $message);
    }

    private function extractCampaignMeta(mixed $meta): array
    {
        if (!is_array($meta)) {
            return [];
        }

        $campaign = $meta['campaign'] ?? null;
        return is_array($campaign) ? $campaign : [];
    }

    /**
     * @return int[]
     */
    private function normalizeRetryBackoffSeconds(mixed $raw): array
    {
        if (is_array($raw)) {
            $normalized = [];
            foreach ($raw as $seconds) {
                $seconds = (int) $seconds;
                if ($seconds < 0) {
                    continue;
                }

                $normalized[] = $seconds;
            }

            if ($normalized !== []) {
                return array_values(array_unique($normalized));
            }
        }

        if (is_string($raw) || is_numeric($raw)) {
            return $this->parseRetryBackoffSeconds((string) $raw);
        }

        $configuredDefault = config('blast.retry.backoff_seconds', [30, 120, 300]);
        if (is_array($configuredDefault)) {
            return $this->normalizeRetryBackoffSeconds($configuredDefault);
        }

        return [30, 120, 300];
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
        iterable $recipients,
        array $deviceLabels = []
    ): array {
        $normalizedChannel = strtoupper($channel);

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

            if ($normalizedChannel === 'EMAIL') {
                $recipientKey = strtolower(trim((string) $recipient->email_wali));
                if ($recipientKey === '') {
                    continue;
                }

                $recipientMap[$recipientKey] = $recipient;
                continue;
            }

            foreach ($this->collectWhatsappTargets($recipient) as $recipientKey) {
                $recipientMap[$recipientKey] = $recipient;
            }
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
            $wibTimestamp = $timestamp?->copy()->timezone(self::WIB_TIMEZONE);
            $status = strtoupper((string) $log->status);
            $statusKey = match ($status) {
                'FAILED' => 'failed',
                'SENT' => 'success',
                default => 'pending',
            };

            $row = [
                'logId' => (int) $log->id,
                'date' => $wibTimestamp ? $wibTimestamp->format('d/m/Y') : '-',
                'time' => $wibTimestamp ? $wibTimestamp->format('H:i:s') : '-',
                'studentName' => $recipient?->nama_siswa ?: '-',
                'studentClass' => $recipient?->kelas ?: '-',
                'parentName' => $recipient?->nama_wali ?: '-',
                'status' => $statusKey,
                'rawStatus' => $status,
                'canRetry' => $status === 'FAILED',
                'canDelete' => true,
                'errorMessage' => trim((string) ($log->error_message ?? '')),
                'responseMessage' => trim((string) ($log->response ?? '')),
                'campaignId' => (string) $log->blast_message_id,
            ];

            if ($normalizedChannel === 'EMAIL') {
                $row['email'] = $target !== '' ? $target : '-';
                $subject = trim((string) ($log->message?->subject ?? ''));
                $row['subject'] = $subject !== '' ? $subject : '-';
                $row['attachments'] = '-';
            } else {
                $row['phone'] = $target !== '' ? $target : '-';
                $deviceId = trim((string) ($log->device_id ?? ''));
                $deviceLabel = $deviceId !== ''
                    ? (string) ($deviceLabels[$deviceId] ?? $deviceId)
                    : '-';
                $row['deviceId'] = $deviceId !== '' ? $deviceId : null;
                $row['deviceLabel'] = $deviceLabel;
            }

            return $row;
        })->values()->all();

        $stats = [
            'total' => count($mappedLogs),
            'sent' => 0,
            'failed' => 0,
            'pending' => 0,
        ];

        foreach ($mappedLogs as $row) {
            $status = (string) ($row['status'] ?? 'pending');
            if ($status === 'success') {
                $stats['sent']++;
                continue;
            }

            if ($status === 'failed') {
                $stats['failed']++;
                continue;
            }

            $stats['pending']++;
        }

        return [
            'stats' => $stats,
            'logs' => $mappedLogs,
        ];
    }

    private function getRecipientsByChannel(string $channel)
    {
        $normalized = strtolower($channel);

        if ($normalized === 'email') {
            $studentRecipients = BlastRecipient::query()
                ->whereNotNull('email_wali')
                ->where('is_valid', true)
                ->orderBy('nama_siswa')
                ->get()
                ->filter(
                    fn (BlastRecipient $recipient) =>
                        filter_var($recipient->email_wali, FILTER_VALIDATE_EMAIL)
                )
                ->values();

            $employeeRecipients = BlastEmployeeRecipient::query()
                ->whereNotNull('email_karyawan')
                ->where('is_valid', true)
                ->orderBy('nama_karyawan')
                ->get()
                ->filter(
                    fn (BlastEmployeeRecipient $employee) =>
                        filter_var($employee->email_karyawan, FILTER_VALIDATE_EMAIL)
                )
                ->map(
                    fn (BlastEmployeeRecipient $employee) =>
                        $this->toPseudoRecipientFromEmployee($employee)
                )
                ->values();

            $employeeYpikRecipients = BlastEmployeeYpikRecipient::query()
                ->whereNotNull('email_karyawan')
                ->where('is_valid', true)
                ->orderBy('nama_karyawan')
                ->get()
                ->filter(
                    fn (BlastEmployeeYpikRecipient $employeeYpik) =>
                        filter_var($employeeYpik->email_karyawan, FILTER_VALIDATE_EMAIL)
                )
                ->map(
                    fn (BlastEmployeeYpikRecipient $employeeYpik) =>
                        $this->toPseudoRecipientFromEmployeeYpik($employeeYpik)
                )
                ->values();

            return $studentRecipients
                ->merge($employeeRecipients)
                ->merge($employeeYpikRecipients)
                ->values();
        }

        $studentRecipients = BlastRecipient::query()
            ->where(function ($query) {
                $query->whereNotNull('wa_wali')
                    ->orWhereNotNull('wa_wali_2');
            })
            ->where('is_valid', true)
            ->orderBy('nama_siswa')
            ->get();

        $employeeRecipients = BlastEmployeeRecipient::query()
            ->whereNotNull('wa_karyawan')
            ->where('is_valid', true)
            ->orderBy('nama_karyawan')
            ->get()
            ->map(
                fn (BlastEmployeeRecipient $employee) =>
                    $this->toPseudoRecipientFromEmployee($employee)
            );

        $employeeYpikRecipients = BlastEmployeeYpikRecipient::query()
            ->whereNotNull('wa_karyawan')
            ->where('is_valid', true)
            ->orderBy('nama_karyawan')
            ->get()
            ->map(
                fn (BlastEmployeeYpikRecipient $employeeYpik) =>
                    $this->toPseudoRecipientFromEmployeeYpik($employeeYpik)
            );

        return $studentRecipients
            ->merge($employeeRecipients)
            ->merge($employeeYpikRecipients)
            ->values();
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
            $scheduledAt = Carbon::parse((string) $validatedData['scheduled_at'], self::WIB_TIMEZONE);
        }
        if (in_array($normalizedChannel, ['whatsapp', 'email'], true)) {
            // Temporarily force blast channels to send immediately.
            $scheduledAt = null;
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

        if (in_array($normalizedChannel, ['whatsapp', 'email'], true)) {
            // Disable delay behavior until campaign flow is stabilized.
            $rateLimitPerMinute = 5000;
            $batchSize = 2000;
            $batchDelaySeconds = 0;
        }

        $retryAttempts = max(
            1,
            (int) ($validatedData['retry_attempts'] ?? config('blast.retry.max_attempts', 3))
        );
        if ($normalizedChannel === 'whatsapp') {
            $retryAttempts = 1;
        }

        $retryBackoffSeconds = $this->parseRetryBackoffSeconds(
            $validatedData['retry_backoff_seconds'] ?? null
        );

        $scheduledDelaySeconds = 0;
        if ($scheduledAt instanceof CarbonInterface) {
            $scheduledDelaySeconds = max(
                0,
                now(self::WIB_TIMEZONE)->diffInSeconds($scheduledAt, false)
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

        $normalizedChannel = strtoupper($channel);
        if (!in_array($normalizedChannel, ['WHATSAPP', 'EMAIL'], true) && !empty($campaignOptions['queue_name'])) {
            $job->onQueue((string) $campaignOptions['queue_name']);
        }

        if (in_array($normalizedChannel, ['WHATSAPP', 'EMAIL'], true)) {
            app(\Illuminate\Contracts\Bus\Dispatcher::class)->dispatchSync($job);
            $dispatchIndex++;
            return;
        }

        $delaySeconds = $this->calculateDispatchDelaySeconds(
            $campaignOptions,
            $dispatchIndex
        );
        if ($delaySeconds > 0) {
            $job->delay(now(self::WIB_TIMEZONE)->addSeconds($delaySeconds));
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

    /**
     * WhatsApp hanya mendukung satu file per pesan.
     * Jika ada file khusus penerima, gunakan itu sebagai prioritas.
     *
     * @param BlastAttachment[] $globalAttachments
     * @param BlastAttachment[] $overrideAttachments
     */
    private function attachWhatsappFilesToPayload(
        BlastPayload $payload,
        array $globalAttachments,
        array $overrideAttachments
    ): void {
        $effective = !empty($overrideAttachments)
            ? $overrideAttachments
            : $globalAttachments;

        $this->attachFilesToPayload($payload, $effective);

        if (!empty($overrideAttachments)) {
            $payload->setMeta('attachment_scope', 'override');
        } elseif (!empty($globalAttachments)) {
            $payload->setMeta('attachment_scope', 'global');
        }
    }

    private function applyDeviceToPayload(
        BlastPayload $payload,
        ?string $deviceId
    ): void {
        if ($deviceId === null || $deviceId === '') {
            return;
        }

        $payload->setMeta('device_id', $deviceId);
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

    /**
     * @return array<string, mixed>
     */
    private function resolveTunggakanContextForRecipient(
        BlastRecipient $recipient,
        TunggakanMessageContextService $tunggakanContextService
    ): array {
        return $tunggakanContextService->resolveForRecipient($recipient);
    }

    private function resolveDbRecipientEmailMessage(
        BlastRecipient $recipient,
        TemplateRenderer $renderer,
        ?BlastMessageTemplate $template,
        string $globalMessage,
        bool $useGlobalDefault,
        array $messageOverrides,
        TunggakanMessageContextService $tunggakanContextService
    ): string {
        $overrideKey = 'db:' . $recipient->id;
        $override = $messageOverrides[$overrideKey] ?? null;
        $mode = strtolower((string) ($override['mode'] ?? ''));
        $customMessage = trim((string) ($override['message'] ?? ''));
        $tunggakanContext = $this->resolveTunggakanContextForRecipient(
            $recipient,
            $tunggakanContextService
        );

        if ($mode === 'manual' && $customMessage !== '') {
            return $customMessage;
        }

        if ($mode === 'template' && $template) {
            return $renderer->render($template->content, $recipient, $tunggakanContext);
        }

        if ($mode === 'global') {
            return $globalMessage;
        }

        if ($useGlobalDefault && trim($globalMessage) !== '') {
            return $globalMessage;
        }

        if ($template) {
            return $renderer->render($template->content, $recipient, $tunggakanContext);
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
        array $messageOverrides,
        TunggakanMessageContextService $tunggakanContextService
    ): string {
        $overrideKey = 'db:' . $recipient->id;
        $override = $messageOverrides[$overrideKey] ?? null;
        $mode = strtolower((string) ($override['mode'] ?? ''));
        $customMessage = trim((string) ($override['message'] ?? ''));
        $tunggakanContext = $this->resolveTunggakanContextForRecipient(
            $recipient,
            $tunggakanContextService
        );

        if ($mode === 'manual' && $customMessage !== '') {
            return $customMessage;
        }

        if ($mode === 'template' && $template) {
            return $renderer->render($template->content, $recipient, $tunggakanContext);
        }

        if ($mode === 'global') {
            return $globalMessage;
        }

        if ($useGlobalDefault && trim($globalMessage) !== '') {
            return $globalMessage;
        }

        if ($template) {
            return $renderer->render($template->content, $recipient, $tunggakanContext);
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

    private function toPseudoRecipientFromEmployee(
        BlastEmployeeRecipient $employee
    ): BlastRecipient {
        return new BlastRecipient([
            'id' => $employee->id,
            'nama_siswa' => (string) $employee->nama_karyawan,
            'kelas' => trim((string) ($employee->instansi ?? '')) !== ''
                ? (string) $employee->instansi
                : 'Karyawan',
            'nama_wali' => trim((string) ($employee->nama_wali ?? '')) !== ''
                ? (string) $employee->nama_wali
                : (string) $employee->nama_karyawan,
            'wa_wali' => $employee->wa_karyawan,
            'wa_wali_2' => null,
            'email_wali' => $employee->email_karyawan,
            'catatan' => $employee->catatan,
            'is_valid' => (bool) $employee->is_valid,
            'source' => 'karyawan',
        ]);
    }

    private function toPseudoRecipientFromEmployeeYpik(
        BlastEmployeeYpikRecipient $employeeYpik
    ): BlastRecipient {
        return new BlastRecipient([
            'id' => $employeeYpik->id,
            'nama_siswa' => (string) $employeeYpik->nama_karyawan,
            'kelas' => trim((string) ($employeeYpik->instansi ?? '')) !== ''
                ? (string) $employeeYpik->instansi
                : 'Karyawan YPIK',
            'nama_wali' => trim((string) ($employeeYpik->nama_wali ?? '')) !== ''
                ? (string) $employeeYpik->nama_wali
                : (string) $employeeYpik->nama_karyawan,
            'wa_wali' => $employeeYpik->wa_karyawan,
            'wa_wali_2' => null,
            'email_wali' => $employeeYpik->email_karyawan,
            'catatan' => $employeeYpik->catatan,
            'is_valid' => (bool) $employeeYpik->is_valid,
            'source' => 'karyawan_ypik',
        ]);
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

    private function maskGatewayApiKey(string $apiKey): string
    {
        $value = trim($apiKey);
        if ($value === '') {
            return '-';
        }

        $visible = 4;
        $length = strlen($value);
        if ($length <= $visible) {
            return str_repeat('*', $length);
        }

        return str_repeat('*', $length - $visible) . substr($value, -$visible);
    }

    /**
     * @return array{0:string,1:\Illuminate\Http\Client\PendingRequest}
     */
    private function buildGatewayClient(): array
    {
        $baseUrl = rtrim(
            (string) config('services.whatsapp_gateway.base_url', ''),
            '/'
        );

        if ($baseUrl === '') {
            throw new \RuntimeException('Gateway base URL belum disetel.');
        }

        $timeout = (int) config('services.whatsapp_gateway.timeout', 20);
        $apiKey = trim((string) config('services.whatsapp_gateway.api_key', ''));
        $apiKeyHeader = trim((string) config('services.whatsapp_gateway.api_key_header', 'X-API-KEY'));

        $headers = [];
        if ($apiKey !== '') {
            $headers[$apiKeyHeader] = $apiKey;
        }

        $client = Http::timeout($timeout)->withHeaders($headers);

        return [$baseUrl, $client];
    }

    private function sanitizeDeviceId(?string $raw): ?string
    {
        $value = trim((string) $raw);
        if ($value === '') {
            return null;
        }

        $normalized = preg_replace('/[^a-zA-Z0-9_-]/', '', $value) ?? '';
        $normalized = strtolower($normalized);

        return $normalized !== '' ? $normalized : null;
    }
}
