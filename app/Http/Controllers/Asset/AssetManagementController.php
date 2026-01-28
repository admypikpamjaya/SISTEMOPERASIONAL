<?php

namespace App\Http\Controllers\Asset;

use App\DTOs\Asset\AssetDataDTO;
use App\DTOs\Asset\RegisterAssetDTO;
use App\DTOs\Asset\RegisterAssetViaFileDTO;
use App\Enums\Asset\AssetCategory;
use App\Http\Controllers\Controller;
use App\Http\Requests\Asset\EditAssetRequest;
use App\Http\Requests\Asset\RegisterAssetRequest;
use App\Http\Requests\Asset\RegisterAssetViaFileRequest;
use App\Services\Asset\AssetService;
use Illuminate\Http\Request;

class AssetManagementController extends Controller
{
    public function __construct(
        private AssetService $service
    ) {}

    public function index(Request $request)
    {
        $page = $request->input('page', 1);
        $pageSize = $request->input('page_size', 10);

        $assets = $this->service->getAssets($request->keyword, ($request->category) ? AssetCategory::from($request->category) : null, $page, $pageSize);
        return view('asset-management.index', [
            'assets' => $assets
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
        $this->service->registerAsset(RegisterAssetDTO::fromArray($request->validated()));

        session()->flash('success', 'Aset berhasil ditambahkan');
        return response()->json(['success' => true]);
    }

    public function storeWithFile(RegisterAssetViaFileRequest $request)
    {
        $this->service->registerAssetViaFile(RegisterAssetViaFileDTO::fromArray($request->validated()));

        session()->flash('success', 'Aset berhasil ditambahkan');
        return response()->json(['success' => true]);
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
            ], $e->getCode() ? $e->getCode() : 500);
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
            ], $e->getCode() ? $e->getCode() : 500);
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
            ], $e->getCode() ? $e->getCode() : 500);
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
