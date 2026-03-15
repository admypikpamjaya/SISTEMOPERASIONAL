<?php

namespace App\Services\Blast;

class WhatsAppProviderSelector
{
    private string $storePath;

    public function __construct()
    {
        $this->storePath = storage_path('app/whatsapp_provider.json');
    }

    public function getProvider(): string
    {
        $provider = $this->readProviderFromStore();
        if ($provider !== null) {
            return $provider;
        }

        return strtolower((string) config('services.whatsapp.provider', 'wablas'));
    }

    public function setProvider(string $provider): void
    {
        $normalized = strtolower(trim($provider));
        $payload = [
            'provider' => $normalized,
            'updated_at' => now()->toDateTimeString(),
        ];

        $directory = dirname($this->storePath);
        if (!is_dir($directory)) {
            @mkdir($directory, 0755, true);
        }

        file_put_contents($this->storePath, json_encode($payload, JSON_PRETTY_PRINT));
    }

    private function readProviderFromStore(): ?string
    {
        if (!is_file($this->storePath)) {
            return null;
        }

        $raw = file_get_contents($this->storePath);
        if ($raw === false) {
            return null;
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return null;
        }

        $provider = strtolower(trim((string) ($decoded['provider'] ?? '')));
        if ($provider === '') {
            return null;
        }

        return $provider;
    }
}
