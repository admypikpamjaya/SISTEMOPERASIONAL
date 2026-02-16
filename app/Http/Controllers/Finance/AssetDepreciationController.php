<?php

namespace App\Http\Controllers\Finance;

use App\DTOs\Finance\DepreciationInputDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\CalculateDepreciationRequest;
use App\Services\Finance\DepreciationService;
use Illuminate\Http\JsonResponse;

class AssetDepreciationController extends Controller
{
    public function __construct(
        private DepreciationService $depreciationService
    ) {}

    public function calculate(CalculateDepreciationRequest $request): JsonResponse
    {
        $input = DepreciationInputDTO::fromArray($request->validated());
        $result = $this->depreciationService->calculateStraightLine($input);

        return response()->json([
            'message' => 'Perhitungan penyusutan berhasil.',
            'data' => $result->toArray(),
        ]);
    }

    public function index()
    {
        return view('finance.depreciation');
    }
}
