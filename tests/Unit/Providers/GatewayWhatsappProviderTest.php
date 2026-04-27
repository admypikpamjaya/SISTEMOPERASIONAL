<?php

namespace Tests\Unit\Providers;

use App\DataTransferObjects\BlastAttachment;
use App\DataTransferObjects\BlastPayload;
use App\Providers\Messaging\GatewayWhatsappProvider;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GatewayWhatsappProviderTest extends TestCase
{
    public function test_send_text_success(): void
    {
        config([
            'services.whatsapp_gateway.base_url' => 'http://gateway.test',
            'services.whatsapp_gateway.api_key' => '',
            'services.whatsapp_gateway.timeout' => 10,
        ]);

        Http::fake(function ($request) {
            $this->assertSame('http://gateway.test/send-message', $request->url());
            $this->assertSame('POST', $request->method());
            $this->assertSame('628123456789', $request['phone']);
            $this->assertSame('Halo', $request['message']);

            return Http::response([
                'success' => true,
                'message' => 'Message queued',
            ], 200);
        });

        $provider = new GatewayWhatsappProvider();
        $payload = new BlastPayload('Halo');

        $result = $provider->send('628123456789', $payload);

        $this->assertTrue($result);
        $this->assertSame('Message queued', $payload->meta['provider_message'] ?? null);
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
            $this->assertSame('http://gateway.test/send-file', $request->url());
            $this->assertSame('POST', $request->method());
            $this->assertSame('secret-key', $request->header('X-API-KEY')[0] ?? null);

            return Http::response([
                'success' => true,
                'message' => 'File queued',
            ], 200);
        });

        $provider = new GatewayWhatsappProvider();
        $payload = new BlastPayload('Lampiran');
        $payload->addAttachment(new BlastAttachment($filePath, 'wa-test.txt', 'text/plain'));

        $result = $provider->send('628123456789', $payload);

        $this->assertTrue($result);
        $this->assertSame('File queued', $payload->meta['provider_message'] ?? null);

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
}
