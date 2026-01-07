<?php 

namespace App\Contracts\Asset;

interface AssetDetailHandler
{
    public function validatePayload(array $data);
    public function getRelationName(): string;
    public function insert(string $assetId, array $data): void;
    public function update(string $assetId, array $data): void;
    public function extractDetailFromCSV(array $row): array;
}