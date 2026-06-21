<?php

namespace Tests\Unit\Providers;

use App\DataTransferObjects\BlastAttachment;
use App\DataTransferObjects\BlastPayload;
use App\Providers\Messaging\GatewayWhatsappProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GatewayWhatsappProviderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_send_text_success(): void
    {
        config([
            'services.whatsapp_gateway.base_url' => 'http://gateway.test',
            'services.whatsapp_gateway.api_key' => '',
            'services.whatsapp_gateway.timeout' => 10,
        ]);

        Http::fake(function ($request) {
            if ($request->url() === 'http://gateway.test/devices') {
                return Http::response([
                    'success' => true,
                    'data' => [
                        'activeDeviceId' => 'default',
                        'devices' => [[
                            'deviceId' => 'default',
                            'status' => 'connected',
                            'user' => ['id' => '628999999999@s.whatsapp.net'],
                        ]],
                    ],
                ], 200);
            }

            $this->assertSame('http://gateway.test/send-message', $request->url());
            $this->assertSame('POST', $request->method());
            $this->assertSame('628123456789', $request['phone']);
            $this->assertSame('Halo', $request['message']);

            return Http::response([
                'success' => true,
                'message' => 'Message sent',
                'data' => [
                    'messageId' => 'wa-message-1',
                    'deliveryStatus' => 'sent',
                    'deviceId' => 'default',
                ],
            ], 200);
        });

        $provider = new GatewayWhatsappProvider();
        $payload = new BlastPayload('Halo');

        $result = $provider->send('628123456789', $payload);

        $this->assertTrue($result);
        $this->assertSame('Message sent', $payload->meta['provider_message'] ?? null);
        $this->assertSame('sent', $payload->meta['provider_delivery_status'] ?? null);
        $this->assertSame('wa-message-1', $payload->meta['provider_reference'] ?? null);
        $this->assertSame('default', $payload->meta['device_id'] ?? null);
    }

    public function test_send_file_success_with_api_key(): void
    {
        $filePath = storage_path('app/wa-test.txt');
        file_put_contents($filePath, 'hello');

        config([
            'services.whatsapp_gateway.base_url' => 'http://gateway.test',
            'services.whatsapp_gateway.api_key' => 'secret-key',
            'services.whatsapp_gateway.api_key_header' => 'X-API-KEY',
            'services.whatsapp_gateway.timeout' => 10,
        ]);

        Http::fake(function ($request) {
            if ($request->url() === 'http://gateway.test/devices') {
                return Http::response([
                    'success' => true,
                    'data' => [
                        'activeDeviceId' => 'default',
                        'devices' => [[
                            'deviceId' => 'default',
                            'status' => 'connected',
                            'user' => ['id' => '628999999999@s.whatsapp.net'],
                        ]],
                    ],
                ], 200);
            }

            $this->assertSame('http://gateway.test/send-file', $request->url());
            $this->assertSame('POST', $request->method());
            $this->assertSame('secret-key', $request->header('X-API-KEY')[0] ?? null);

            return Http::response([
                'success' => true,
                'message' => 'File sent',
                'data' => [
                    'messageId' => 'wa-file-1',
                    'deliveryStatus' => 'sent',
                    'deviceId' => 'default',
                ],
            ], 200);
        });

        $provider = new GatewayWhatsappProvider();
        $payload = new BlastPayload('Lampiran');
        $payload->addAttachment(new BlastAttachment($filePath, 'wa-test.txt', 'text/plain'));

        $result = $provider->send('628123456789', $payload);

        $this->assertTrue($result);
        $this->assertSame('File sent', $payload->meta['provider_message'] ?? null);
        $this->assertSame('sent', $payload->meta['provider_delivery_status'] ?? null);
        $this->assertSame('wa-file-1', $payload->meta['provider_reference'] ?? null);

        if (is_file($filePath)) {
            unlink($filePath);
        }
    }

    public function test_send_returns_false_on_gateway_rejected(): void
    {
        config([
            'services.whatsapp_gateway.base_url' => 'http://gateway.test',
            'services.whatsapp_gateway.api_key' => '',
            'services.whatsapp_gateway.timeout' => 10,
        ]);

        Http::fake([
            'http://gateway.test/devices' => Http::response([
                'success' => true,
                'data' => [
                    'activeDeviceId' => 'default',
                    'devices' => [[
                        'deviceId' => 'default',
                        'status' => 'connected',
                        'user' => ['id' => '628999999999@s.whatsapp.net'],
                    ]],
                ],
            ], 200),
            '*' => Http::response([
                'success' => false,
                'message' => 'Rejected',
            ], 200),
        ]);

        $provider = new GatewayWhatsappProvider();
        $payload = new BlastPayload('Halo');

        $result = $provider->send('628123456789', $payload);

        $this->assertFalse($result);
        $this->assertSame('Rejected', $payload->meta['provider_error'] ?? null);
    }

    public function test_selected_device_is_kept_even_when_target_matches_sender_number(): void
    {
        config([
            'services.whatsapp_gateway.base_url' => 'http://gateway-switch.test',
            'services.whatsapp_gateway.api_key' => '',
            'services.whatsapp_gateway.timeout' => 10,
        ]);

        Http::fake(function ($request) {
            if ($request->url() === 'http://gateway-switch.test/devices') {
                return Http::response([
                    'success' => true,
                    'data' => [
                        'activeDeviceId' => 'default',
                        'devices' => [
                            [
                                'deviceId' => 'default',
                                'status' => 'connected',
                                'user' => ['id' => '62895333867173:43@s.whatsapp.net'],
                            ],
                            [
                                'deviceId' => 'device-alternate',
                                'status' => 'connected',
                                'user' => ['id' => '6287888370352:23@s.whatsapp.net'],
                            ],
                        ],
                    ],
                ], 200);
            }

            $this->assertSame('http://gateway-switch.test/send-message', $request->url());
            $this->assertSame('default', $request['deviceId']);

            return Http::response([
                'success' => true,
                'message' => 'Message queued',
            ], 200);
        });

        $provider = new GatewayWhatsappProvider();
        $payload = new BlastPayload('Halo');
        $payload->setMeta('device_id', 'default');

        $result = $provider->send('62895333867173', $payload);

        $this->assertTrue($result);
        $this->assertSame('default', $payload->meta['device_id'] ?? null);
        $this->assertSame('62895333867173', $payload->meta['provider_sender_phone'] ?? null);
    }

    public function test_selected_disconnected_device_is_rejected(): void
    {
        config([
            'services.whatsapp_gateway.base_url' => 'http://gateway-block.test',
            'services.whatsapp_gateway.api_key' => '',
            'services.whatsapp_gateway.timeout' => 10,
        ]);

        Http::fake([
            'http://gateway-block.test/devices' => Http::response([
                'success' => true,
                'data' => [
                    'activeDeviceId' => 'default',
                    'devices' => [[
                        'deviceId' => 'default',
                        'status' => 'disconnected',
                        'user' => ['id' => '62895333867173:43@s.whatsapp.net'],
                    ]],
                ],
            ], 200),
        ]);

        $provider = new GatewayWhatsappProvider();
        $payload = new BlastPayload('Halo');
        $payload->setMeta('device_id', 'default');

        $result = $provider->send('628123456789', $payload);

        $this->assertFalse($result);
        $this->assertStringContainsString(
            'tidak terhubung',
            $payload->meta['provider_error'] ?? ''
        );
        Http::assertSentCount(1);
    }
}
