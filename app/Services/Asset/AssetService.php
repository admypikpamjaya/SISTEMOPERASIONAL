<?php 

namespace App\Services\Asset;

use App\DTOs\Asset\AssetDataDTO;
use App\DTOs\Asset\RegisterAssetDTO;
use App\DTOs\Asset\RegisterAssetViaFileDTO;
use App\DTOs\File\DownloadFileDTO;
use App\Enums\Asset\AssetCategory;
use App\Enums\Asset\AssetUnit;
use App\Models\Asset\Asset;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class AssetService 
{
    private const CHUNK_SIZE = 50;

    private function extractDataFromCSV(AssetCategory $category, $file): array
    {
        $dtos = [];

        $handle = fopen($file->getRealPath(), 'r');
        $headers = array_map('trim', fgetcsv($handle));

        $requiredHeaders = [
            'account_code',
            'unit',
            'location',
        ];
        foreach ($requiredHeaders as $required) 
        {
            if(!in_array($required, $headers)) 
                throw new \Exception("Format header CSV tidak valid");
        }

        $handler = AssetFactory::createHandler($category);
        $rowNumber = 1;

        while (($row = fgetcsv($handle)) !== false) 
        {
            $rowNumber++;
            $row = array_combine($headers, $row);

            if(empty($row['account_code'])) 
                throw new \Exception("Kode akun kosong di baris ke-{$rowNumber}");

            if(empty($row['location'])) 
                throw new \Exception("Lokasi kosong di baris ke-{$rowNumber}");

            $detail = $handler->extractDetailFromCsv($row);

            $dtos[] = new RegisterAssetDTO(
                category: $category,
                accountCode: $row['account_code'],
                serialNumber: $row['serial_number'] ?? null,
                unit: $row['unit'],
                location: $row['location'],
                purchaseYear: $row['purchase_year'] ?? null,
                detail: $detail
            );
        }

        fclose($handle);

        return $dtos;
    }

    private function makeSafeFilename(string $name): string
    {
        return preg_replace('/[^A-Za-z0-9_\-]/', '_', $name);
    }

    public function getAssets(?string $keyword = null, ?AssetCategory $category = null, ?AssetUnit $unit = null, ?int $page = 1, ?int $pageSize = 10)
    {
        $query = Asset::query();
        if($keyword)
        {
            $query->where(function($q) use ($keyword) {
                $q->where('account_code', 'like', "%{$keyword}%")
                ->orWhere('location', 'like', "%{$keyword}%");
            });
        }

        if($category)
        {
            $query->where('category', $category);
        }

        if($unit)
        {
            $query->where('unit', $unit);
        }

        return $query
            ->orderBy('created_at', 'desc')
            ->paginate($pageSize, ['*'], 'page', $page)
            ->appends(array_filter([
                'keyword' => $keyword,
                'category' => $category?->value,
            ]));
    }

    public function getAsset(string $id)
    {
        $asset = Asset::find($id);
        if(empty($asset))
            throw new \Exception('Asset tidak ditemukan', 404);
        
        $data = $asset->loadWithRelation();
        return AssetDataDTO::fromModel($data);
    }

    public function registerAsset(RegisterAssetDTO $dto)
    {
        DB::beginTransaction();
        try
        {
            $assetData = Arr::except($dto->toArray(), 'detail');
            $validatedAssetData = Asset::validateRegistrationPayload($assetData);

            $asset = Asset::create($validatedAssetData);
            $asset->qr_code_path = $asset->generateQRCode();
            $asset->save();

            $assetDetailHandler = AssetFactory::createHandler($dto->category);

            $validatedDetail =$assetDetailHandler->validatePayload($dto->detail);
            $assetDetailHandler->insert($asset->id, $validatedDetail);

            DB::commit();
            return $asset->loadWithRelation();
        }
        catch(\Throwable $e)
        {
            DB::rollback();
            throw $e;
        }
    }

    public function registerAssetViaFile(RegisterAssetViaFileDTO $dto)
    {
        DB::beginTransaction();
        try
        {
            $data = $this->extractDataFromCSV($dto->category, $dto->file);
            $chunks = array_chunk($data, self::CHUNK_SIZE);

            foreach ($chunks as $chunkIndex => $chunk) 
            {
                foreach ($chunk as $rowIndex => $assetDTO) 
                {
                    try 
                    {
                        $this->registerAsset($assetDTO);
                    } 
                    catch (\Throwable $e) 
                    {
                        throw new \Exception(
                            "Gagal import CSV di baris ke-" .
                            (($chunkIndex * self::CHUNK_SIZE) + $rowIndex + 2) .
                            ": " . $e->getMessage(),
                            previous: $e
                        );
                    }
                }
            }
            DB::commit();
        }
        catch(\Throwable $e)
        {
            DB::rollback();
            throw $e;
        }
    }

    public function updateAsset(string $id, AssetDataDTO $dto)
    {
        $asset = Asset::find($id);
        if(empty($asset))
            throw new \Exception('Asset tidak ditemukan', 404);
        
        $asset->update([
            'account_code' => $dto->accountCode,
            'serial_number' => $dto->serialNumber,
            'unit' => $dto->unit,
            'location' => $dto->location,
            'purchase_year' => $dto->purchaseYear
        ]);

        $handler = AssetFactory::createHandler($asset->category);
        $validatedDetail = $handler->validatePayload($dto->detail);
        $handler->update($asset->id, $validatedDetail);

        return $asset->loadWithRelation();
    }

    public function deleteAsset(string $id)
    {
        $asset = Asset::find($id);
        if(empty($asset))
            throw new \Exception('Asset tidak ditemukan', 404);
        
        DB::transaction(function () use ($asset) {
            $disk = Storage::disk('public');

            if($disk->exists($asset->qr_code_path)) 
                $disk->delete($asset->qr_code_path);

            $asset->delete();
        });
    }

    public function bulkDelete(array $ids)
    {
        DB::transaction(function () use ($ids) {
            foreach ($ids as $id) {
                $this->deleteAsset($id);
            }
        });
    }

    public function downloadQrCode(array $ids)
    {
        $disk = Storage::disk('public');

        $query = Asset::whereNotNull('qr_code_path');

        if(!empty($ids))
            $query->whereIn('id', $ids);

        $assets = $query->get();

        if($assets->isEmpty()) 
            throw new \Exception('QR Code tidak ditemukan');

        /**
         * =============================
         * SINGLE FILE
         * =============================
         */
        if($assets->count() === 1) 
        {
            $asset = $assets->first();

            if(!$disk->exists($asset->qr_code_path))
                throw new \Exception('QR Code tidak ditemukan');

            $filename = $this->makeSafeFilename($asset->account_code) . '.svg';
            return new DownloadFileDTO(
                filename: $filename,
                mimeType: 'image/svg+xml',
                content: $disk->get($asset->qr_code_path)
            );
        }

        /**
         * =============================
         * MULTIPLE → ZIP
         * =============================
         */
        $zip = new ZipArchive();
        $tmpPath = tempnam(sys_get_temp_dir(), 'qr_');

        $zip->open($tmpPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        foreach ($assets as $asset) 
        {
            if($disk->exists($asset->qr_code_path)) 
            {
                $filename = $this->makeSafeFilename($asset->account_code) . '.svg';
                $zip->addFromString(
                    $filename,
                    $disk->get($asset->qr_code_path)
                );
            }
        }

        $zip->close();

        return new DownloadFileDTO(
            filename: 'qr-codes.zip',
            mimeType: 'application/zip',
            content: file_get_contents($tmpPath)
        );
    }
}