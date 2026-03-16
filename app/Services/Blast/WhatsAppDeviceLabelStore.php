<?php

namespace App\Services\Blast;

class WhatsAppDeviceLabelStore
{
    private string $storePath;

    public function __construct()
    {
        $this->storePath = storage_path('app/whatsapp_device_labels.json');
    }

    /**
     * @return array<string, string>
     */
    public function getLabels(): array
    {
        $data = $this->readStore();
        $labels = $data['labels'] ?? [];
        return is_array($labels) ? $labels : [];
    }

    public function getLabel(string $deviceId): ?string
    {
        $labels = $this->getLabels();
        $label = $labels[$deviceId] ?? null;
        $label = is_string($label) ? trim($label) : '';
        return $label !== '' ? $label : null;
    }

    public function setLabel(string $deviceId, string $label): void
    {
        $labels = $this->getLabels();
        $labels[$deviceId] = trim($label);
        $this->writeStore(['labels' => $labels]);
    }

    public function removeLabel(string $deviceId): void
    {
        $labels = $this->getLabels();
        if (array_key_exists($deviceId, $labels)) {
            unset($labels[$deviceId]);
            $this->writeStore(['labels' => $labels]);
        }
    }

    public function clearLabels(): void
    {
        $this->writeStore(['labels' => []]);
    }

    /**
     * @return array<string, mixed>
     */
    private function readStore(): array
    {
        if (!is_file($this->storePath)) {
            return ['labels' => []];
        }

        $raw = file_get_contents($this->storePath);
        if ($raw === false) {
            return ['labels' => []];
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return ['labels' => []];
        }

        return $decoded;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function writeStore(array $data): void
    {
        $directory = dirname($this->storePath);
        if (!is_dir($directory)) {
            @mkdir($directory, 0755, true);
        }

        $payload = [
            'labels' => $data['labels'] ?? [],
            'updated_at' => now()->toDateTimeString(),
        ];

        file_put_contents($this->storePath, json_encode($payload, JSON_PRETTY_PRINT));
    }
}
