<?php

namespace Tests\Unit\Services\Blast;

use App\DataTransferObjects\BlastPayload;
use App\Jobs\Blast\SendWhatsappBlastJob;
use App\Models\BlastLog;
use App\Models\BlastMessage;
use App\Services\Blast\WhatsAppBlastService;
use App\Services\Blast\WhatsAppGatewayJobStatusService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class WhatsAppGatewayJobStatusServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'services.whatsapp_gateway.base_url' => 'http://gateway.test',
            'services.whatsapp_gateway.api_key' => '',
            'services.whatsapp_gateway.timeout' => 10,
        ]);

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        Schema::create('blast_messages', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('channel');
            $table->text('message');
            $table->string('campaign_status')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('blast_logs', function (Blueprint $table) {
            $table->id();
            $table->string('blast_message_id');
            $table->string('device_id')->nullable();
            $table->string('status');
            $table->string('provider_status')->nullable();
            $table->string('provider_reference')->nullable();
            $table->string('provider_message_id')->nullable();
            $table->string('provider_sender_phone')->nullable();
            $table->timestamp('provider_checked_at')->nullable();
            $table->text('response')->nullable();
            $table->text('error_message')->nullable();
            $table->unsignedInteger('attempt')->default(0);
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function test_it_promotes_completed_gateway_job_to_sent(): void
    {
        $message = BlastMessage::query()->create([
            'channel' => 'WHATSAPP',
            'message' => 'Test',
            'campaign_status' => 'RUNNING',
        ]);

        $log = BlastLog::query()->create([
            'blast_message_id' => $message->id,
            'status' => 'PENDING',
            'provider_status' => 'queued',
            'provider_reference' => '587',
            'response' => 'Message queued',
        ]);

        Http::fake([
            'http://gateway.test/jobs/status' => Http::response([
                'success' => true,
                'data' => [
                    'jobs' => [[
                        'jobId' => '587',
                        'state' => 'completed',
                        'messageId' => 'WA-MESSAGE-ID',
                        'finishedOn' => 1782210000000,
                    ]],
                ],
            ]),
        ]);

        app(WhatsAppGatewayJobStatusService::class)->syncPendingLogs();

        $log->refresh();
        $message->refresh();

        $this->assertSame('SENT', $log->status);
        $this->assertSame('completed', $log->provider_status);
        $this->assertSame('WA-MESSAGE-ID', $log->provider_message_id);
        $this->assertNotNull($log->sent_at);
        $this->assertSame('COMPLETED', $message->campaign_status);
    }

    public function test_queued_gateway_response_stays_pending(): void
    {
        $message = BlastMessage::query()->create([
            'channel' => 'WHATSAPP',
            'message' => 'Test',
            'campaign_status' => 'RUNNING',
        ]);

        $log = BlastLog::query()->create([
            'blast_message_id' => $message->id,
            'status' => 'PENDING',
            'response' => null,
        ]);

        $payload = new BlastPayload('Test');
        $payload->setMeta('blast_log_id', $log->id);
        $payload->setMeta('blast_message_id', $message->id);
        $payload->setMeta('device_id', 'default');
        $payload->setMeta('provider_message', 'Message queued');
        $payload->setMeta('provider_delivery_status', 'queued');
        $payload->setMeta('provider_reference', '587');
        $payload->setMeta('provider_sender_phone', '62895333867173');

        $service = $this->mock(WhatsAppBlastService::class);
        $service->shouldReceive('send')
            ->once()
            ->with('6287888370352', $payload)
            ->andReturnTrue();

        (new SendWhatsappBlastJob('6287888370352', $payload))->handle($service);

        $log->refresh();

        $this->assertSame('PENDING', $log->status);
        $this->assertSame('queued', $log->provider_status);
        $this->assertSame('587', $log->provider_reference);
        $this->assertSame('62895333867173', $log->provider_sender_phone);
        $this->assertNull($log->sent_at);
    }

    public function test_it_records_gateway_failure_as_failed(): void
    {
        $message = BlastMessage::query()->create([
            'channel' => 'WHATSAPP',
            'message' => 'Test',
            'campaign_status' => 'RUNNING',
        ]);

        $log = BlastLog::query()->create([
            'blast_message_id' => $message->id,
            'status' => 'PENDING',
            'provider_status' => 'queued',
            'provider_reference' => '588',
            'response' => 'Message queued',
        ]);

        Http::fake([
            'http://gateway.test/jobs/status' => Http::response([
                'success' => true,
                'data' => [
                    'jobs' => [[
                        'jobId' => '588',
                        'state' => 'failed',
                        'failedReason' => 'WhatsApp not connected',
                        'finishedOn' => 1782210000000,
                    ]],
                ],
            ]),
        ]);

        app(WhatsAppGatewayJobStatusService::class)->syncPendingLogs();

        $log->refresh();

        $this->assertSame('FAILED', $log->status);
        $this->assertSame('failed', $log->provider_status);
        $this->assertSame('WhatsApp not connected', $log->error_message);
    }
}
