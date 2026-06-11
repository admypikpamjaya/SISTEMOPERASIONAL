<?php

namespace App\Services\Asset\AssetDetailHandlers;

use App\Contracts\Asset\AssetDetailHandler;

class NoDetailHandler implements AssetDetailHandler
{
    public function validatePayload(array $data): array
    {
        return [];
    }

    public function getRelationName(): string
    {
        return '';
    }

    public function insert(string $assetId, array $data): void
    {
    }

    public function update(string $assetId, array $data): void
    {
    }

    public function extractDetailFromCSV(array $row): array
    {
        return [];
    }
}
