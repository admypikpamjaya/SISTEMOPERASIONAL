<?php

namespace App\Http\Controllers;

use App\Enums\Portal\PortalPermission;
use App\Enums\User\UserRole;
use App\Models\AppSetting;
use App\Models\BlastEmailAccount;
use App\Models\BlastLog;
use App\Models\BlastMessage;
use App\Models\FeatureFlag;
use App\Models\LoginHistory;
use App\Models\RolePermissionOverride;
use App\Models\SystemAccessLog;
use App\Models\SystemBlastLogArchive;
use App\Models\User;
use App\Services\AccessControl\PermissionService;
use App\Services\SystemManagement\ClientContextService;
use App\Services\SystemManagement\FeatureAvailabilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class SystemManagementController extends Controller
{
    private const WIB_TIMEZONE = 'Asia/Jakarta';

    public function login()
    {
        return view('auth.system-management-login');
    }

    public function authenticate(Request $request, ClientContextService $clientContext)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $credentials = [
            'email' => strtolower(trim((string) $validated['email'])),
            'password' => (string) $validated['password'],
        ];

        if (!Auth::attempt($credentials, false)) {
            return back()
                ->with('auth_failed', 'Email atau password Sistem Management salah.')
                ->withInput($request->only('email'));
        }

        if (Auth::user()?->role !== UserRole::SYSTEM_MANAGEMENT->value) {
            Auth::logout();

            return back()
                ->with('auth_failed', 'Halaman ini hanya untuk akun Sistem Management.')
                ->withInput($request->only('email'));
        }

        $request->session()->regenerate();

        if (Schema::hasTable('login_histories')) {
            LoginHistory::query()->create(array_merge(
                $clientContext->fromRequest($request),
                [
                    'user_id' => (string) Auth::id(),
                    'session_id' => (string) $request->session()->getId(),
                    'locale' => (string) app()->getLocale(),
                    'logged_in_at' => now(),
                ]
            ));
        }

        return redirect()->intended(route('system-management.index'));
    }

    public function index()
    {
        return $this->renderPage('overview', [
            'systems' => $this->systemStatuses(),
            'blastFlows' => $this->blastFlows(),
        ]);
    }

    public function status()
    {
        return $this->renderPage('status', [
            'systems' => $this->systemStatuses(),
        ]);
    }

    public function maintenance()
    {
        return $this->renderPage('maintenance');
    }

    public function blastFlow()
    {
        return $this->renderPage('blast-flow', [
            'blastFlows' => $this->blastFlows(),
        ]);
    }

    public function audit()
    {
        return $this->renderPage('audit', [
            'accessLogs' => $this->accessLogs(),
            'loginHistories' => $this->loginHistories(),
        ]);
    }

    public function users()
    {
        return $this->renderPage('users', [
            'users' => User::query()->orderBy('role')->orderBy('name')->get(['id', 'name', 'email', 'role']),
        ]);
    }

    public function permissions(PermissionService $permissionService)
    {
        return $this->renderPage('permissions', [
            'permissionMatrix' => $this->permissionMatrix($permissionService),
            'roles' => UserRole::cases(),
            'permissions' => PortalPermission::cases(),
        ]);
    }

    public function ai()
    {
        return $this->renderPage('ai', [
            'aiDraft' => session('ai_draft'),
            'aiExecutionResult' => session('ai_execution_result'),
        ]);
    }

    public function apiTester()
    {
        return $this->renderPage('api-tester', [
            'apiTesterResult' => session('api_tester_result'),
        ]);
    }

    public function cms()
    {
        return $this->renderPage('cms', [
            'cms' => $this->cmsSettings(),
        ]);
    }

    public function features()
    {
        return $this->renderPage('features', [
            'featureFlags' => $this->featureFlags(),
        ]);
    }

    public function featureAccess(FeatureAvailabilityService $featureAvailability)
    {
        return $this->renderPage('feature-access', [
            'availableFeatures' => $featureAvailability->featuresWithState(),
        ]);
    }

    public function updateFeatureAccess(Request $request, FeatureAvailabilityService $featureAvailability)
    {
        $featureKeys = collect($featureAvailability->features())
            ->keys()
            ->implode(',');

        $validated = $request->validate([
            'feature_key' => ['required', 'string', 'in:' . $featureKeys],
            'is_enabled' => ['required', 'boolean'],
        ]);

        $feature = $featureAvailability->features()[(string) $validated['feature_key']] ?? null;
        if (is_array($feature) && (bool) ($feature['locked'] ?? false)) {
            return back()->withErrors([
                'feature_key' => 'Fitur Sistem Management tidak dapat dinonaktifkan.',
            ]);
        }

        $featureAvailability->setEnabled(
            (string) $validated['feature_key'],
            (bool) $validated['is_enabled'],
            Auth::user()
        );

        return back()->with('success', 'Akses fitur berhasil diperbarui.');
    }

    public function archives()
    {
        return $this->renderPage('archives', [
            'archivedBlastLogs' => $this->archivedBlastLogs(),
        ]);
    }

    public function resetUserPassword(Request $request, User $user)
    {
        $validated = $request->validate([
            'password' => ['required', 'string', 'min:12'],
        ], [
            'password.min' => 'Password minimal :min karakter untuk aksi Sistem Management.',
        ]);

        $user->forceFill([
            'password' => Hash::make((string) $validated['password']),
        ])->save();

        return back()->with('success', 'Password ' . $user->email . ' berhasil direset.');
    }

    public function updatePermission(Request $request)
    {
        $validated = $request->validate([
            'role' => ['required', 'string'],
            'permission' => ['required', 'string'],
            'allowed' => ['nullable', 'boolean'],
        ]);

        abort_if($validated['role'] === UserRole::SYSTEM_MANAGEMENT->value, 422, 'Akses Sistem Management selalu penuh dan tidak dapat dibatasi.');
        abort_unless(
            collect(UserRole::cases())->contains(fn (UserRole $role) => $role->value === $validated['role']),
            422
        );
        abort_unless(
            collect(PortalPermission::cases())->contains(fn (PortalPermission $permission) => $permission->value === $validated['permission']),
            422
        );

        RolePermissionOverride::query()->updateOrCreate(
            [
                'role' => (string) $validated['role'],
                'permission' => (string) $validated['permission'],
            ],
            [
                'allowed' => $request->boolean('allowed'),
                'updated_by' => Auth::id(),
            ]
        );

        return back()->with('success', 'Restrict halaman role berhasil diperbarui.');
    }

    public function storeFeature(Request $request)
    {
        $validated = $request->validate([
            'key' => ['required', 'string', 'max:120'],
            'name' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:5000'],
            'rollout_notes' => ['nullable', 'string', 'max:5000'],
            'ai_prompt' => ['nullable', 'string', 'max:10000'],
        ]);

        $key = Str::slug((string) $validated['key'], '_');

        FeatureFlag::query()->updateOrCreate(
            ['key' => $key],
            [
                'name' => (string) $validated['name'],
                'description' => $validated['description'] ?? null,
                'rollout_notes' => $validated['rollout_notes'] ?? null,
                'ai_prompt' => $validated['ai_prompt'] ?? null,
                'status' => 'draft',
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]
        );

        return back()->with('success', 'Draft fitur berhasil disimpan.');
    }

    public function toggleFeature(Request $request, FeatureFlag $featureFlag)
    {
        $validated = $request->validate([
            'is_enabled' => ['required', 'boolean'],
        ]);

        $featureFlag->update([
            'is_enabled' => (bool) $validated['is_enabled'],
            'status' => $request->boolean('is_enabled') ? 'enabled' : 'disabled',
            'updated_by' => Auth::id(),
        ]);

        return back()->with('success', 'Status fitur berhasil diperbarui.');
    }

    public function updateMaintenance(Request $request)
    {
        $validated = $request->validate([
            'enabled' => ['nullable', 'boolean'],
            'message' => ['nullable', 'string', 'max:500'],
        ]);

        AppSetting::query()->updateOrCreate(
            ['key' => 'system.maintenance'],
            [
                'value' => [
                    'enabled' => $request->boolean('enabled'),
                    'message' => trim((string) ($validated['message'] ?? '')) ?: 'Sistem sedang maintenance berkala. Silakan coba lagi beberapa saat lagi.',
                    'updated_by' => Auth::id(),
                    'updated_at' => now()->toDateTimeString(),
                ],
            ]
        );

        return back()->with('success', 'Mode maintenance berhasil diperbarui.');
    }

    public function sendApiRequest(Request $request)
    {
        $validated = $request->validate([
            'method' => ['required', 'in:GET,POST,PUT,PATCH,DELETE,HEAD,OPTIONS'],
            'url' => ['required', 'url', 'max:2000'],
            'headers_json' => ['nullable', 'string', 'max:10000'],
            'body_type' => ['nullable', 'in:none,json,form,raw'],
            'body' => ['nullable', 'string', 'max:100000'],
            'timeout' => ['nullable', 'integer', 'min:1', 'max:60'],
        ]);

        $url = trim((string) $validated['url']);
        if (!$this->isAllowedApiTesterUrl($url)) {
            return back()->withErrors([
                'api_tester' => 'URL API tidak diizinkan. Gunakan HTTP/HTTPS dan cek konfigurasi private network.',
            ])->withInput();
        }

        try {
            $headers = $this->parseHeadersJson((string) ($validated['headers_json'] ?? ''));
            $method = (string) $validated['method'];
            $bodyType = (string) ($validated['body_type'] ?? 'none');
            $body = (string) ($validated['body'] ?? '');
            $timeout = (int) ($validated['timeout'] ?? 15);
            $startedAt = microtime(true);

            $pending = Http::timeout($timeout)
                ->connectTimeout(min(10, $timeout))
                ->withHeaders($headers);

            $options = [];
            if (!in_array($method, ['GET', 'HEAD'], true) && trim($body) !== '') {
                if ($bodyType === 'json') {
                    $decoded = json_decode($body, true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        return back()->withErrors([
                            'api_tester' => 'Body JSON tidak valid: ' . json_last_error_msg(),
                        ])->withInput();
                    }
                    $options['json'] = $decoded;
                } elseif ($bodyType === 'form') {
                    parse_str($body, $formData);
                    $options['form_params'] = $formData;
                } elseif ($bodyType === 'raw') {
                    $options['body'] = $body;
                }
            }

            $response = $pending->send($method, $url, $options);
            $durationMs = (int) round((microtime(true) - $startedAt) * 1000);

            return back()->with('api_tester_result', [
                'ok' => $response->successful(),
                'method' => $method,
                'url' => $url,
                'status' => $response->status(),
                'duration_ms' => $durationMs,
                'headers' => $response->headers(),
                'body' => $this->truncateApiResponse($response->body()),
            ])->withInput();
        } catch (\Throwable $exception) {
            return back()->with('api_tester_result', [
                'ok' => false,
                'method' => $validated['method'],
                'url' => $url,
                'status' => 'ERROR',
                'duration_ms' => 0,
                'headers' => [],
                'body' => $exception->getMessage(),
            ])->withInput();
        }
    }

    public function updateCms(Request $request)
    {
        $validated = $request->validate([
            'brand_short' => ['nullable', 'string', 'max:80'],
            'sidebar_label' => ['nullable', 'string', 'max:100'],
            'notice_enabled' => ['nullable', 'boolean'],
            'notice_text' => ['nullable', 'string', 'max:500'],
            'content_width' => ['nullable', 'in:default,wide,compact'],
            'custom_css' => ['nullable', 'string', 'max:20000'],
        ]);

        AppSetting::query()->updateOrCreate(
            ['key' => 'system.cms'],
            [
                'value' => [
                    'brand_short' => trim((string) ($validated['brand_short'] ?? '')),
                    'sidebar_label' => trim((string) ($validated['sidebar_label'] ?? '')),
                    'notice_enabled' => $request->boolean('notice_enabled'),
                    'notice_text' => trim((string) ($validated['notice_text'] ?? '')),
                    'content_width' => (string) ($validated['content_width'] ?? 'default'),
                    'custom_css' => $this->sanitizeCustomCss((string) ($validated['custom_css'] ?? '')),
                    'updated_by' => Auth::id(),
                    'updated_at' => now()->toDateTimeString(),
                ],
            ]
        );

        return back()->with('success', 'CMS tampilan berhasil diperbarui.');
    }

    public function draftFeatureWithAi(Request $request)
    {
        $validated = $request->validate([
            'module' => ['required', 'string', 'max:120'],
            'goal' => ['required', 'string', 'max:2000'],
        ]);

        $draft = $this->buildAiFeatureDraft(
            (string) $validated['module'],
            (string) $validated['goal']
        );

        return back()->with('ai_draft', $draft);
    }

    public function executeAiAction(Request $request)
    {
        $validated = $request->validate([
            'mode' => ['required', 'in:plan,apply'],
            'target_scope' => ['required', 'string', 'max:300'],
            'instruction' => ['required', 'string', 'max:10000'],
        ]);

        $endpoint = trim((string) config('system_management.ai_executor.endpoint', ''));
        if ($endpoint === '') {
            return back()->with('ai_execution_result', [
                'ok' => false,
                'status' => 'AI executor belum tersambung',
                'body' => 'Isi AI_FEATURE_EXECUTOR_ENDPOINT dan AI_FEATURE_EXECUTOR_TOKEN jika ingin AI menjalankan perubahan fitur langsung dari web.',
            ]);
        }

        try {
            $pending = Http::timeout((int) config('system_management.ai_executor.timeout', 60))
                ->acceptJson();

            $token = trim((string) config('system_management.ai_executor.token', ''));
            if ($token !== '') {
                $pending = $pending->withToken($token);
            }

            $response = $pending->post($endpoint, [
                'mode' => (string) $validated['mode'],
                'target_scope' => (string) $validated['target_scope'],
                'instruction' => (string) $validated['instruction'],
                'repository_path' => base_path(),
                'requested_by' => Auth::user()?->only(['id', 'name', 'email', 'role']),
                'safety' => [
                    'require_git_diff' => true,
                    'require_tests' => true,
                    'system_management_only' => true,
                ],
            ]);

            return back()->with('ai_execution_result', [
                'ok' => $response->successful(),
                'status' => $response->status(),
                'body' => $this->truncateApiResponse($response->body()),
            ]);
        } catch (\Throwable $exception) {
            return back()->with('ai_execution_result', [
                'ok' => false,
                'status' => 'ERROR',
                'body' => $exception->getMessage(),
            ]);
        }
    }

    private function renderPage(string $page, array $data = [])
    {
        return view('system-management.index', array_merge([
            'page' => $page,
            'maintenance' => $this->maintenanceState(),
        ], $data));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function systemStatuses(): array
    {
        $statuses = [];

        $statuses[] = $this->statusItem('Laravel App', true, app()->environment(), 'Aplikasi web berhasil memuat controller.');

        try {
            DB::select('select 1');
            $statuses[] = $this->statusItem('Database', true, DB::connection()->getDriverName(), 'Koneksi database aktif.');
        } catch (\Throwable $exception) {
            $statuses[] = $this->statusItem('Database', false, 'down', $exception->getMessage());
        }

        try {
            $key = 'system-management-health:' . Str::random(8);
            Cache::put($key, 'ok', now()->addMinute());
            $statuses[] = $this->statusItem('Cache', Cache::get($key) === 'ok', config('cache.default'), 'Cache read/write dicek langsung.');
        } catch (\Throwable $exception) {
            $statuses[] = $this->statusItem('Cache', false, 'down', $exception->getMessage());
        }

        $statuses[] = $this->statusItem(
            'Queue Laravel',
            strtolower((string) config('queue.default')) !== 'sync',
            (string) config('queue.default'),
            strtolower((string) config('queue.default')) === 'sync'
                ? 'Masih sync. Untuk blast besar gunakan database/redis queue worker.'
                : 'Queue asynchronous aktif.'
        );

        $statuses[] = $this->statusItem(
            'Storage Logs',
            is_writable(storage_path('logs')),
            is_writable(storage_path('logs')) ? 'writable' : 'locked',
            storage_path('logs')
        );

        $statuses[] = $this->whatsappGatewayStatus();
        $statuses[] = $this->emailStatus();
        $statuses[] = $this->statusItem(
            'Maintenance Mode',
            !($this->maintenanceState()['enabled'] ?? false),
            ($this->maintenanceState()['enabled'] ?? false) ? 'maintenance' : 'normal',
            'Jika aktif, hanya Sistem Management yang tetap bisa masuk.'
        );

        return $statuses;
    }

    /**
     * @return array<string, mixed>
     */
    private function whatsappGatewayStatus(): array
    {
        $baseUrl = rtrim((string) config('services.whatsapp_gateway.base_url', ''), '/');
        if ($baseUrl === '') {
            return $this->statusItem('WhatsApp Gateway', false, 'not configured', 'WHATSAPP_GATEWAY_BASE_URL kosong.');
        }

        try {
            $headers = [];
            $apiKey = trim((string) config('services.whatsapp_gateway.api_key', ''));
            if ($apiKey !== '') {
                $headers[(string) config('services.whatsapp_gateway.api_key_header', 'X-API-KEY')] = $apiKey;
            }

            $response = Http::timeout(3)->withHeaders($headers)->get($baseUrl . '/health');
            $data = $response->json('data') ?? [];

            return $this->statusItem(
                'WhatsApp Gateway',
                $response->successful(),
                (string) ($data['deliveryMode'] ?? $response->status()),
                'Queue: ' . (string) ($data['queueName'] ?? '-') . ', worker: ' . ((bool) ($data['workerEnabled'] ?? false) ? 'aktif' : 'mati')
            );
        } catch (\Throwable $exception) {
            return $this->statusItem('WhatsApp Gateway', false, 'unreachable', $exception->getMessage());
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function emailStatus(): array
    {
        $activeAccount = Schema::hasTable('blast_email_accounts')
            ? BlastEmailAccount::query()->where('is_enabled', true)->where('is_active', true)->first()
            : null;

        $configured = (string) config('mail.default') !== '';

        return $this->statusItem(
            'Email Blast',
            $configured,
            $activeAccount ? $activeAccount->senderLabel() : (string) config('mail.default'),
            $activeAccount ? 'Akun email blast aktif.' : 'Menggunakan konfigurasi mail default.'
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function statusItem(string $name, bool $ok, string $state, string $detail): array
    {
        return compact('name', 'ok', 'state', 'detail');
    }

    private function accessLogs()
    {
        if (!Schema::hasTable('system_access_logs')) {
            return collect();
        }

        return SystemAccessLog::query()
            ->with('user:id,name,email,role')
            ->latest('occurred_at')
            ->limit(80)
            ->get();
    }

    private function loginHistories()
    {
        if (!Schema::hasTable('login_histories')) {
            return collect();
        }

        return LoginHistory::query()
            ->with('user:id,name,email,role')
            ->latest('logged_in_at')
            ->limit(30)
            ->get();
    }

    private function archivedBlastLogs()
    {
        if (!Schema::hasTable('system_blast_log_archives')) {
            return collect();
        }

        return SystemBlastLogArchive::query()
            ->with('archivedBy:id,name,email,role')
            ->latest('id')
            ->limit(50)
            ->get();
    }

    private function featureFlags()
    {
        if (!Schema::hasTable('feature_flags')) {
            return collect();
        }

        return FeatureFlag::query()
            ->latest('updated_at')
            ->limit(50)
            ->get();
    }

    /**
     * @return array<string, mixed>
     */
    private function blastFlows(): array
    {
        if (!Schema::hasTable('blast_messages')) {
            return ['whatsapp' => collect(), 'email' => collect(), 'stats' => []];
        }

        $campaigns = BlastMessage::query()
            ->withCount([
                'logs as total_logs',
                'logs as sent_logs' => fn ($query) => $query->where('status', 'SENT'),
                'logs as failed_logs' => fn ($query) => $query->where('status', 'FAILED'),
                'logs as pending_logs' => fn ($query) => $query->where('status', 'PENDING'),
            ])
            ->latest('created_at')
            ->limit(16)
            ->get(['id', 'channel', 'subject', 'campaign_status', 'priority', 'created_at', 'started_at', 'completed_at']);

        $providerPending = Schema::hasTable('blast_logs')
            ? BlastLog::query()->whereIn('provider_status', ['queued', 'waiting', 'active', 'delayed', 'pending'])->count()
            : 0;

        return [
            'whatsapp' => $campaigns->where('channel', 'WHATSAPP')->values(),
            'email' => $campaigns->where('channel', 'EMAIL')->values(),
            'stats' => [
                'provider_pending' => $providerPending,
                'failed_total' => Schema::hasTable('blast_logs') ? BlastLog::query()->where('status', 'FAILED')->count() : 0,
                'pending_total' => Schema::hasTable('blast_logs') ? BlastLog::query()->where('status', 'PENDING')->count() : 0,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function permissionMatrix(PermissionService $permissionService): array
    {
        $matrix = [];
        $overrides = Schema::hasTable('role_permission_overrides')
            ? RolePermissionOverride::query()->get()->groupBy('role')
            : collect();

        foreach (UserRole::cases() as $role) {
            $probe = new User(['role' => $role->value]);
            $allowed = collect($permissionService->getAccessForUser($probe));

            $matrix[$role->value] = [
                'allowed' => $allowed->flip()->all(),
                'overrides' => $overrides->get($role->value, collect())->keyBy('permission'),
            ];
        }

        return $matrix;
    }

    /**
     * @return array{enabled:bool,message:string,updated_by:mixed,updated_at:mixed}
     */
    private function maintenanceState(): array
    {
        if (!Schema::hasTable('app_settings')) {
            return [
                'enabled' => false,
                'message' => 'Sistem sedang maintenance berkala. Silakan coba lagi beberapa saat lagi.',
                'updated_by' => null,
                'updated_at' => null,
            ];
        }

        $setting = AppSetting::query()->where('key', 'system.maintenance')->first();
        $value = is_array($setting?->value) ? $setting->value : [];

        return [
            'enabled' => (bool) ($value['enabled'] ?? false),
            'message' => (string) ($value['message'] ?? 'Sistem sedang maintenance berkala. Silakan coba lagi beberapa saat lagi.'),
            'updated_by' => $value['updated_by'] ?? null,
            'updated_at' => $value['updated_at'] ?? null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function cmsSettings(): array
    {
        if (!Schema::hasTable('app_settings')) {
            return $this->defaultCmsSettings();
        }

        $setting = AppSetting::query()->where('key', 'system.cms')->first();
        $value = is_array($setting?->value) ? $setting->value : [];

        return array_merge($this->defaultCmsSettings(), $value);
    }

    /**
     * @return array<string, mixed>
     */
    private function defaultCmsSettings(): array
    {
        return [
            'brand_short' => '',
            'sidebar_label' => '',
            'notice_enabled' => false,
            'notice_text' => '',
            'content_width' => 'default',
            'custom_css' => '',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function parseHeadersJson(string $raw): array
    {
        $raw = trim($raw);
        if ($raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            throw new \InvalidArgumentException('Headers harus JSON object, contoh {"Accept":"application/json"}.');
        }

        $headers = [];
        foreach ($decoded as $key => $value) {
            $key = trim((string) $key);
            if ($key === '' || in_array(strtolower($key), ['host', 'content-length'], true)) {
                continue;
            }

            $headers[$key] = is_scalar($value) ? (string) $value : json_encode($value);
        }

        return $headers;
    }

    private function isAllowedApiTesterUrl(string $url): bool
    {
        $parts = parse_url($url);
        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host = trim((string) ($parts['host'] ?? ''));
        if (!in_array($scheme, ['http', 'https'], true) || $host === '') {
            return false;
        }

        if ((bool) config('system_management.api_tester.allow_private_network', true)) {
            return true;
        }

        $addresses = gethostbynamel($host) ?: [$host];
        foreach ($addresses as $address) {
            if (filter_var(
                $address,
                FILTER_VALIDATE_IP,
                FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
            ) === false) {
                return false;
            }
        }

        return true;
    }

    private function sanitizeCustomCss(string $css): string
    {
        return str_ireplace(
            ['</style', '<script', '</script'],
            ['/* style-close-blocked */', '/* script-blocked */', '/* script-close-blocked */'],
            $css
        );
    }

    private function truncateApiResponse(string $body): string
    {
        $maxBytes = (int) config('system_management.api_tester.max_response_bytes', 200000);
        if (strlen($body) <= $maxBytes) {
            return $body;
        }

        return substr($body, 0, $maxBytes) . "\n\n/* Response dipotong sampai {$maxBytes} byte. */";
    }

    /**
     * @return array<string, string>
     */
    private function buildAiFeatureDraft(string $module, string $goal): array
    {
        $endpoint = trim((string) config('system_management.ai_feature_builder.endpoint', ''));
        if ($endpoint !== '') {
            try {
                $request = Http::timeout((int) config('system_management.ai_feature_builder.timeout', 20))
                    ->acceptJson();

                $token = trim((string) config('system_management.ai_feature_builder.token', ''));
                if ($token !== '') {
                    $request = $request->withToken($token);
                }

                $response = $request->post($endpoint, [
                    'module' => $module,
                    'goal' => $goal,
                    'context' => [
                        'framework' => 'Laravel 10',
                        'requires_audit_log' => true,
                        'requires_feature_flag' => true,
                    ],
                ]);

                if ($response->successful() && is_array($response->json())) {
                    $payload = $response->json();
                    return array_merge(
                        $this->buildLocalAiFeatureDraft($module, $goal),
                        array_filter([
                            'key' => $payload['key'] ?? null,
                            'name' => $payload['name'] ?? null,
                            'description' => $payload['description'] ?? null,
                            'ai_prompt' => $payload['ai_prompt'] ?? $payload['prompt'] ?? null,
                            'rollout_notes' => $payload['rollout_notes'] ?? null,
                        ])
                    );
                }
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        return $this->buildLocalAiFeatureDraft($module, $goal);
    }

    /**
     * @return array<string, string>
     */
    private function buildLocalAiFeatureDraft(string $module, string $goal): array
    {
        $key = Str::slug($module . ' ' . Str::limit($goal, 40, ''), '_');

        return [
            'key' => $key,
            'name' => Str::headline(str_replace('_', ' ', $key)),
            'description' => 'Draft fitur untuk modul ' . $module . ': ' . $goal,
            'ai_prompt' => "Modul: {$module}\nTujuan: {$goal}\nBuat fungsi, validasi, permission, audit log, dan rollback plan sebelum implementasi.",
            'rollout_notes' => 'Mulai sebagai draft feature flag, aktifkan hanya setelah migrasi dan test selesai.',
        ];
    }
}
