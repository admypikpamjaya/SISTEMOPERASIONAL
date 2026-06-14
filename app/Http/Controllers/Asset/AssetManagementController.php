<?php

namespace App\Http\Controllers\Asset;

use App\DTOs\Asset\AssetDataDTO;
use App\DTOs\Asset\RegisterAssetDTO;
use App\DTOs\Asset\RegisterAssetViaFileDTO;
use App\Enums\Asset\AssetCategory;
use App\Enums\Asset\AssetUnit;
use App\Http\Controllers\Controller;
use App\Http\Requests\Asset\EditAssetRequest;
use App\Http\Requests\Asset\RegisterAssetRequest;
use App\Http\Requests\Asset\RegisterAssetViaFileRequest;
use App\Services\Asset\AssetService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Asset master controller.
 *
 * The current module manages inventory identity and operational detail such as
 * category, account code, unit, location, purchase year, and per-category
 * detail. It does not yet persist the finance policy data needed to automate
 * end-of-period depreciation.
 */
class AssetManagementController extends Controller
{
    public function __construct(
        private AssetService $service
    ) {}

    private function errorStatusCode(\Throwable $throwable): int
    {
        $code = (int) $throwable->getCode();

        return ($code >= 400 && $code <= 599) ? $code : 500;
    }

    public function index(Request $request)
    {
        return $this->renderAssetIndex(
            request: $request,
            forcedCategory: null,
            pageMode: 'master',
            pageRouteName: 'asset-management.index'
        );
    }

    public function ac(Request $request): View
    {
        return $this->renderCategoryIndex($request, AssetCategory::AC, 'asset-management.ac.index');
    }

    public function buildingInfrastructure(Request $request): View
    {
        return $this->renderCategoryIndex(
            $request,
            AssetCategory::BUILDING_INFRASTRUCTURE,
            'asset-management.building-infrastructure.index'
        );
    }

    public function electronic(Request $request): View
    {
        return $this->renderCategoryIndex($request, AssetCategory::ELECTRONIC, 'asset-management.electronic.index');
    }

    public function roomInventory(Request $request): View
    {
        return $this->renderCategoryIndex($request, AssetCategory::ROOM_INVENTORY, 'asset-management.room-inventory.index');
    }

    public function vehicle(Request $request): View
    {
        return $this->renderCategoryIndex($request, AssetCategory::VEHICLE, 'asset-management.vehicle.index');
    }

    public function computer(Request $request): View
    {
        return $this->renderCategoryIndex($request, AssetCategory::COMPUTER, 'asset-management.computer.index');
    }

    private function renderCategoryIndex(Request $request, AssetCategory $category, string $pageRouteName): View
    {
        return $this->renderAssetIndex(
            request: $request,
            forcedCategory: $category,
            pageMode: 'category',
            pageRouteName: $pageRouteName
        );
    }

    private function renderAssetIndex(
        Request $request,
        ?AssetCategory $forcedCategory,
        string $pageMode,
        string $pageRouteName
    ): View
    {
        $page = $request->input('page', 1);
        $pageSize = $request->input('page_size', 10);
        $selectedCategory = $forcedCategory
            ?? ($request->category ? AssetCategory::from($request->category) : null);

        $assets = $this->service->getAssets(
            $request->keyword, 
            $selectedCategory,
            ($request->unit) ? AssetUnit::from($request->unit) : null,
            $page, 
            $pageSize,
            $request->recorded_from,
            $request->recorded_until,
            $request->import_file
        );
        return view('asset-management.index', [
            'assets' => $assets,
            'assetPageMode' => $pageMode,
            'assetPageCategory' => $selectedCategory,
            'assetPageRouteName' => $pageRouteName,
        ]);
    }

    public function showRegisterForm(Request $request)
    {
        return view('asset-management.register-form');
    }

    public function showEditForm(string $id)
    {
        try 
        {
            return view('asset-management.edit-form', [
                'asset' => $this->service->getAsset($id)
            ]);
        }
        catch(\Exception $e)
        {
            session()->flash('error', $e->getMessage());
            return redirect()->route('asset-management.index');
        }
    }

    public function store(RegisterAssetRequest $request)
    {
        // Registration currently creates the asset master only. Any future
        // depreciation policy flow should be added in the service layer so the
        // create transaction stays consistent.
        $this->service->registerAsset(RegisterAssetDTO::fromArray($request->validated()));

        session()->flash('success', 'Aset berhasil ditambahkan');
        return response()->json(['success' => true]);
    }

    public function storeWithFile(RegisterAssetViaFileRequest $request)
    {
        try {
            $dto = RegisterAssetViaFileDTO::fromArray($request->validated());
            $summary = $this->service->registerAssetViaFile($dto);

            $sheetSummary = ($summary['source_type'] ?? 'csv') === 'excel'
                ? ' dari ' . ($summary['sheet_count'] ?? 0) . ' sheet aktif'
                : '';
            $message = 'Sinkronisasi aset ' . $dto->category->label() . ' berhasil. '
                . ($summary['created_rows'] ?? 0) . ' data baru ditambahkan dan '
                . ($summary['updated_rows'] ?? 0) . ' data lama diperbarui'
                . $sheetSummary . '.';

            session()->flash('success', $message);

            return response()->json([
                'success' => true,
                'message' => $message,
                'summary' => $summary,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], $this->errorStatusCode($e));
        }
    }

    public function downloadTemplate(string $category)
    {
        $category = strtoupper(trim($category));
        $templateMap = [
            AssetCategory::AC->value => [
                'path' => resource_path('asset-templates/ac/LIST AC SEKRETARIAT YPIK.xlsx'),
                'download_name' => 'template-import-ac-ypik.xlsx',
            ],
            AssetCategory::COMPUTER->value => [
                'path' => resource_path('asset-templates/computer/LIST KOMPUTER (REV) (3).xlsx'),
                'download_name' => 'template-import-komputer-ypik.xlsx',
            ],
            AssetCategory::BUILDING_INFRASTRUCTURE->value => [
                'path' => resource_path('asset-templates/building-infrastructure/template_kategori_bangunan_prasarana_kosong.xlsx'),
                'download_name' => 'template-import-bangunan-prasarana-ypik.xlsx',
            ],
            AssetCategory::ELECTRONIC->value => [
                'path' => resource_path('asset-templates/electronic/template_kategori_elektronik.xlsx'),
                'download_name' => 'template-import-elektronik-ypik.xlsx',
            ],
            AssetCategory::ROOM_INVENTORY->value => [
                'path' => resource_path('asset-templates/room-inventory/template_kategori_inventaris_ruangan.xlsx'),
                'download_name' => 'template-import-inventaris-ruangan-ypik.xlsx',
            ],
            AssetCategory::VEHICLE->value => [
                'path' => resource_path('asset-templates/vehicle/template_kategori_kendaraan.xlsx'),
                'download_name' => 'template-import-kendaraan-ypik.xlsx',
            ],
        ];

        if (!array_key_exists($category, $templateMap)) {
            return redirect()
                ->route('asset-management.index')
                ->with('error', 'Template untuk kategori tersebut belum tersedia.');
        }

        $templatePath = $templateMap[$category]['path'];
        if (!is_file($templatePath)) {
            return redirect()
                ->route('asset-management.index')
                ->with('error', 'File template tidak ditemukan.');
        }

        return response()->download(
            $templatePath,
            $templateMap[$category]['download_name']
        );
    }

    public function update(EditAssetRequest $request)
    {
        try 
        {
            $assetDTO = AssetDataDTO::fromArray($request->validated());
            $this->service->updateAsset($assetDTO->id, $assetDTO);

            session()->flash('success', 'Aset berhasil diupdate');
            return response()->json(['success' => true]);
        }
        catch(\Throwable $e)
        {
            return response()->json([
                'message' => $e->getMessage()
            ], $this->errorStatusCode($e));
        }
    }

    public function delete(string $id)
    {
        try 
        {
            $this->service->deleteAsset($id);

            session()->flash('success', 'Aset berhasil dihapus');
            return response()->json(['success' => true]);
        }
        catch(\Throwable $e)
        {
            return response()->json([
                'message' => $e->getMessage()
            ], $this->errorStatusCode($e));
        }
    }

    public function bulkDelete(Request $request)
    {
        try 
        {
            $ids = $request->input('ids', []);
            if(count($ids) === 0)
                return response()->json([
                    'message' => 'Tidak ada data yang dipilih'
                ], 400);

            $this->service->bulkDelete($ids);

            session()->flash('success', 'Aset berhasil dihapus');
            return response()->json(['success' => true]);
        }
        catch(\Throwable $e)
        {
            return response()->json([
                'message' => $e->getMessage()
            ], $this->errorStatusCode($e));
        }
    }

    public function showQrCode(string $id): View
    {
        try {
            return view('asset-management.qr-code', $this->service->getAssetQrCodeDetail($id));
        } catch (\Throwable $e) {
            return redirect()
                ->route('asset-management.index')
                ->with('error', $e->getMessage());
        }
    }

    public function downloadQrCode(Request $request)
    {
        try 
        {
            $ids = $request->input('ids', []);
            $file = $this->service->downloadQrCode($ids);

            return response($file->content, 200, [
                'Content-Type' => $file->mimeType,
                'Content-Disposition' => 'attachment; filename="'.$file->filename.'"',
            ]);
        }
        catch(\Throwable $e) 
        {
            return redirect()->route('asset-management.index')->with('error', $e->getMessage());
        }
    }
}
