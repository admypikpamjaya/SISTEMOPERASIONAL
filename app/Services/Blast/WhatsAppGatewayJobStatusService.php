<?php

namespace App\Services\Blast;

use App\Models\BlastLog;
use App\Models\BlastMessage;
use Carbon\Carbon;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppGatewayJobStatusService
{
    private const DONE_STATES = [
        'completed',
        'delivered',
        'delivery_ack',
        'done',
        'played',
        'read',
        'sent',
        'server_ack',
        'success',
    ];

    private const PENDING_STATES = [
        'active',
        'delayed',
        'paused',
        'pending',
        'prioritized',
        'queued',
        'waiting',
        'waiting-children',
    ];

    public function syncPendingLogs(int $limit = 100): void
    {
        $this->promoteAcknowledgedPendingLogs($limit);

        $unsupportedCacheKey = 'whatsapp-gateway-job-status-unsupported';
        if (Cache::get($unsupportedCacheKey) === true) {
            return;
        }

        $logs = BlastLog::query()
            ->whereHas('message', fn ($query) => $query->where('channel', 'WHATSAPP'))
            ->whereNotNull('provider_reference')
            ->where(function ($query) {
                $query->where('status', 'PENDING')
                    ->orWhereIn('provider_status', self::PENDING_STATES);
            })
            ->where(function ($query) {
                $query->whereNull('provider_checked_at')
                    ->orWhere('provider_checked_at', '<=', now()->subSeconds(5));
            })
            ->oldest('provider_checked_at')
            ->limit(max(1, min($limit, 200)))
            ->get();

        if ($logs->isEmpty()) {
            return;
        }

        $references = $logs
            ->pluck('provider_reference')
            ->map(fn ($reference) => trim((string) $reference))
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($references === []) {
            return;
        }

        try {
            [$baseUrl, $client] = $this->buildGatewayClient();
            $response = $client->post($baseUrl . '/jobs/status', [
                'jobIds' => $references,
            ]);
        } catch (\Throwable $exception) {
            Log::warning('[WA GATEWAY JOB STATUS UNREACHABLE]', [
                'error' => $exception->getMessage(),
            ]);
            return;
        }

        if (!$response->successful()) {
            if ($response->status() === 404) {
                Cache::put($unsupportedCacheKey, true, now()->addMinute());
                return;
            }

            Log::warning('[WA GATEWAY JOB STATUS FAILED]', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return;
        }

        $payload = $response->json();
        $jobs = is_array($payload['data']['jobs'] ?? null)
            ? $payload['data']['jobs']
            : [];

        if ($jobs === []) {
            return;
        }

        $jobsByReference = collect($jobs)->keyBy(
            fn ($job) => trim((string) ($job['jobId'] ?? ''))
        );

        $campaignIds = collect();
        foreach ($logs as $log) {
            $reference = trim((string) $log->provider_reference);
            $job = $jobsByReference->get($reference);
            if (!is_array($job)) {
                continue;
            }

            $this->applyJobStatus($log, $job);
            $campaignIds->push((string) $log->blast_message_id);
        }

        $this->refreshCampaigns($campaignIds);
    }

    private function applyJobStatus(BlastLog $log, array $job): void
    {
        $state = strtolower(trim((string) ($job['state'] ?? 'unknown')));
        $updates = [
            'provider_status' => $state,
            'provider_checked_at' => now(),
        ];

        $messageId = trim((string) ($job['messageId'] ?? ''));
        if ($messageId !== '') {
            $updates['provider_message_id'] = $messageId;
        }

        if (in_array($state, self::DONE_STATES, true)) {
            $updates['status'] = 'SENT';
            $updates['error_message'] = null;
            $updates['response'] = 'Message completed by WhatsApp gateway.';
            $updates['sent_at'] = $this->timestampFromMilliseconds(
                $job['finishedOn'] ?? null
            ) ?? now();
        } elseif ($state === 'failed') {
            $reason = trim((string) ($job['failedReason'] ?? ''));
            if ($reason === '') {
                $reason = 'Gateway worker failed to send the WhatsApp message.';
            }

            $updates['status'] = 'FAILED';
            $updates['error_message'] = $reason;
            $updates['response'] = $reason;
            $updates['sent_at'] = $this->timestampFromMilliseconds(
                $job['finishedOn'] ?? null
            ) ?? now();
        } elseif (in_array($state, self::PENDING_STATES, true)) {
            $updates['status'] = 'PENDING';
            $updates['sent_at'] = null;
        }

        $log->update($updates);
    }

    private function promoteAcknowledgedPendingLogs(int $limit): void
    {
        $logs = BlastLog::query()
            ->whereHas('message', fn ($query) => $query->where('channel', 'WHATSAPP'))
            ->where('status', 'PENDING')
            ->latest('updated_at')
            ->limit(max(1, min($limit, 200)))
            ->get();

        if ($logs->isEmpty()) {
            return;
        }

        $campaignIds = collect();
        foreach ($logs as $log) {
            $providerStatus = strtolower(trim((string) ($log->provider_status ?? '')));
            $hasProviderMessageId = trim((string) ($log->provider_message_id ?? '')) !== '';
            $isPendingProvider = in_array($providerStatus, self::PENDING_STATES, true);
            $isFailedProvider = $providerStatus === 'failed';
            $isDone = in_array($providerStatus, self::DONE_STATES, true)
                || ($hasProviderMessageId && !$isPendingProvider && !$isFailedProvider);

            if (!$isDone) {
                continue;
            }

            $log->update([
                'status' => 'SENT',
                'provider_status' => $providerStatus !== '' ? $providerStatus : 'sent',
                'provider_checked_at' => now(),
                'error_message' => null,
                'response' => trim((string) ($log->response ?? '')) !== ''
                    ? $log->response
                    : 'Message completed by WhatsApp gateway.',
                'sent_at' => $log->sent_at ?? now(),
            ]);

            $campaignIds->push((string) $log->blast_message_id);
        }

        $this->refreshCampaigns($campaignIds);
    }

    private function refreshCampaigns(Collection $campaignIds): void
    {
        foreach ($campaignIds->filter()->unique() as $campaignId) {
            $pendingExists = BlastLog::query()
                ->where('blast_message_id', $campaignId)
                ->where('status', 'PENDING')
                ->exists();

            if ($pendingExists) {
                continue;
            }

            BlastMessage::query()
                ->whereKey($campaignId)
                ->whereNotIn('campaign_status', ['PAUSED', 'STOPPED'])
                ->update([
                    'campaign_status' => 'COMPLETED',
                    'completed_at' => now(),
                ]);
        }
    }

    private function timestampFromMilliseconds(mixed $value): ?Carbon
    {
        if (!is_numeric($value) || (int) $value <= 0) {
            return null;
        }

        return Carbon::createFromTimestampMs((int) $value);
    }

    /**
     * @return array{0:string,1:PendingRequest}
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

        $headers = [];
        $apiKey = trim((string) config('services.whatsapp_gateway.api_key', ''));
        if ($apiKey !== '') {
            $header = trim((string) config(
                'services.whatsapp_gateway.api_key_header',
                'X-API-KEY'
            ));
            $headers[$header] = $apiKey;
        }

        $timeout = (int) config('services.whatsapp_gateway.timeout', 20);
        $client = Http::timeout($timeout)
            ->connectTimeout($timeout)
            ->withHeaders($headers);

        return [$baseUrl, $client];
    }
}
