<?php

namespace App\Http\Controllers\Finance;

use App\DTOs\Finance\DepreciationInputDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\CalculateDepreciationRequest;
use App\Models\Asset\Asset;
use App\Models\FinanceDepreciationCalculationLog;
use App\Services\Finance\DepreciationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Throwable;

class AssetDepreciationController extends Controller
{
    public function __construct(
        private DepreciationService $depreciationService
    ) {}

    public function calculate(CalculateDepreciationRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $input = DepreciationInputDTO::fromArray($validated);
            $result = $this->depreciationService->calculateStraightLine($input);
            $calculatedAt = now(config('app.timezone'));
            $logId = null;
            $logPayload = null;
            $loggingAvailable = Schema::hasTable('finance_depreciation_calculation_logs');

            if ($loggingAvailable) {
                $log = FinanceDepreciationCalculationLog::query()->create([
                    'asset_id' => $validated['asset_id'],
                    'period_month' => (int) $validated['month'],
                    'period_year' => (int) $validated['year'],
                    'acquisition_cost' => $result->acquisitionCost,
                    'useful_life_months' => $result->usefulLifeMonths,
                    'depreciation_per_month' => $result->depreciationPerMonth,
                    'calculated_by' => auth()->id() ? (string) auth()->id() : null,
                    'calculated_at' => $calculatedAt,
                ]);
                $logId = $log->id;
                $log->loadMissing([
                    'asset:id,account_code',
                    'calculator:id,name',
                ]);

                $logPayload = [
                    'id' => $log->id,
                    'calculated_at_label' => $log->calculated_at?->timezone(config('app.timezone'))->format('d/m/Y H:i:s'),
                    'asset_id' => $log->asset_id,
                    'asset_account_code' => $log->asset?->account_code ?? '-',
                    'period_label' => sprintf('%02d/%04d', (int) $log->period_month, (int) $log->period_year),
                    'acquisition_cost' => (float) $log->acquisition_cost,
                    'useful_life_months' => (int) $log->useful_life_months,
                    'depreciation_per_month' => (float) $log->depreciation_per_month,
                    'calculated_by_name' => $log->calculator?->name ?? '-',
                ];
            }

            $message = $loggingAvailable
                ? 'Perhitungan penyusutan berhasil dan log tersimpan.'
                : 'Perhitungan penyusutan berhasil, tetapi log belum tersimpan (tabel log belum tersedia).';

            return response()->json([
                'message' => $message,
                'data' => array_merge($result->toArray(), [
                    'period_month' => (int) $validated['month'],
                    'period_year' => (int) $validated['year'],
                    'calculated_at' => $calculatedAt->format('Y-m-d H:i:s'),
                    'log_id' => $logId,
                    'log_saved' => $logId !== null,
                    'logging_available' => $loggingAvailable,
                    'log' => $logPayload,
                ]),
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'Gagal menghitung penyusutan aset.',
            ], 500);
        }
    }

    public function index()
    {
        try {
            $assets = Asset::query()
                ->select('id', 'account_code', 'category', 'location')
                ->orderBy('account_code')
                ->get();
            $logs = new Collection();
            if (Schema::hasTable('finance_depreciation_calculation_logs')) {
                $logs = FinanceDepreciationCalculationLog::query()
                    ->with([
                        'asset:id,account_code,category,location',
                        'calculator:id,name',
                    ])
                    ->orderByDesc('calculated_at')
                    ->limit(50)
                    ->get();
            }

            return view('finance.depreciation', [
                'assets' => $assets,
                'logs' => $logs,
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('finance.dashboard')
                ->with('error', 'Gagal memuat halaman penyusutan aset.');
        }
    }
}
