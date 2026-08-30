<?php

namespace Tests\Unit\Providers;

use App\DataTransferObjects\BlastPayload;
use App\Providers\Messaging\WablasWhatsappProvider;
use Illuminate\Support\Facades\Http;
use Silvanix\Wablas\Check as WablasCheck;
use Silvanix\Wablas\Device as WablasDevice;
use Tests\TestCase;

class WablasWhatsappProviderTest extends TestCase
{
    public function test_send_text_uses_configured_wablas_endpoint_and_token(): void
    {
        config([
            'services.wablas.token' => 'wablas-token',
            'services.wablas.secret_key' => 'wablas-secret',
            'services.wablas.base_url' => 'https://jkt.wablas.com',
            'services.wablas.server' => null,
        ]);

        Http::fake(function ($request) {
            $this->assertSame('https://jkt.wablas.com/api/send-message', $request->url());
            $this->assertSame('POST', $request->method());
            $this->assertSame('wablas-token.wablas-secret', $request->header('Authorization')[0] ?? null);
            $this->assertSame('628123456789', $request['phone']);
            $this->assertSame('Halo', $request['message']);

            return Http::response([
                'status' => true,
                'message' => 'success',
                'data' => [
                    'messages' => [[
                        'status' => 'sent',
                    ]],
                ],
            ], 200);
        });

        $provider = new WablasWhatsappProvider();
        $payload = new BlastPayload('Halo');

        $result = $provider->send('628123456789', $payload);

        $this->assertTrue($result);
        $this->assertSame('success', $payload->meta['provider_message'] ?? null);
        $this->assertSame('sent', $payload->meta['provider_delivery_status'] ?? null);
        Http::assertSentCount(1);
    }

    public function test_pending_wablas_response_remains_pending_and_keeps_message_reference(): void
    {
        config([
            'services.wablas.token' => 'wablas-token',
            'services.wablas.secret_key' => 'wablas-secret',
            'services.wablas.base_url' => 'https://jkt.wablas.com',
            'services.wablas.server' => null,
        ]);

        Http::fake([
            'https://jkt.wablas.com/api/send-message' => Http::response([
                'status' => true,
                'message' => 'Message is pending and waiting to be processed',
                'data' => [
                    'messages' => [[
                        'id' => 'wablas-message-123',
                        'status' => 'pending',
                    ]],
                ],
            ], 200),
        ]);

        $payload = new BlastPayload('Halo');
        $result = (new WablasWhatsappProvider())->send('628123456789', $payload);

        $this->assertTrue($result);
        $this->assertSame('pending', $payload->meta['provider_delivery_status'] ?? null);
        $this->assertSame('wablas-message-123', $payload->meta['provider_reference'] ?? null);
        $this->assertSame('wablas-message-123', $payload->meta['provider_message_id'] ?? null);
    }

    public function test_send_returns_false_when_wablas_token_is_missing(): void
    {
        config([
            'services.wablas.token' => '',
            'services.wablas.secret_key' => '',
            'services.wablas.base_url' => 'https://jkt.wablas.com',
            'services.wablas.server' => null,
        ]);

        Http::fake();

        $provider = new WablasWhatsappProvider();
        $payload = new BlastPayload('Halo');

        $result = $provider->send('628123456789', $payload);

        $this->assertFalse($result);
        $this->assertSame('Wablas token belum dikonfigurasi.', $payload->meta['provider_error'] ?? null);
        Http::assertNothingSent();
    }

    public function test_string_false_status_is_not_treated_as_success(): void
    {
        config([
            'services.wablas.token' => 'wablas-token',
            'services.wablas.secret_key' => 'wablas-secret',
            'services.wablas.base_url' => 'https://jkt.wablas.com',
            'services.wablas.server' => null,
        ]);

        Http::fake([
            'https://jkt.wablas.com/api/send-message' => Http::response([
                'status' => 'false',
                'message' => 'device disconnected',
            ], 200),
        ]);

        $provider = new WablasWhatsappProvider();
        $payload = new BlastPayload('Halo');

        $result = $provider->send('628123456789', $payload);

        $this->assertFalse($result);
        $this->assertSame('device disconnected', $payload->meta['provider_error'] ?? null);
    }

    public function test_device_info_uses_raw_token_without_secret(): void
    {
        config([
            'services.wablas.token' => 'wablas-token',
            'services.wablas.secret_key' => 'wablas-secret',
            'services.wablas.base_url' => 'https://jkt.wablas.com',
            'services.wablas.server' => null,
        ]);

        Http::fake(function ($request) {
            $this->assertSame(
                'https://jkt.wablas.com/api/device/info?token=wablas-token',
                $request->url()
            );

            return Http::response([
                'status' => true,
                'data' => [
                    'status' => 'connected',
                ],
            ], 200);
        });

        $result = (new WablasDevice())->info();

        $this->assertTrue($result['status'] ?? false);
    }

    public function test_phone_check_uses_raw_token_and_wablas_host(): void
    {
        config([
            'services.wablas.token' => 'wablas-token',
            'services.wablas.secret_key' => 'wablas-secret',
            'services.wablas.base_url' => 'https://jkt.wablas.com',
            'services.wablas.server' => null,
        ]);

        Http::fake(function ($request) {
            $this->assertSame(
                'https://phone.wablas.com/check-phone-number?phones=628123456789',
                $request->url()
            );
            $this->assertSame('wablas-token', $request->header('Authorization')[0] ?? null);
            $this->assertSame('https://jkt.wablas.com', $request->header('Url')[0] ?? null);

            return Http::response([
                'status' => 'success',
                'message' => 'Success',
            ], 200);
        });

        $result = (new WablasCheck())->phone('628123456789');

        $this->assertSame('success', $result['status'] ?? null);
    }
}
