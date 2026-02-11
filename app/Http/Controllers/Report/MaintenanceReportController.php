<?php

namespace App\Http\Controllers\Report;

use App\DTOs\Report\CreateMaintenanceReportDTO;
use App\DTOs\Report\UpdateMaintenanceReportDTO;
use App\DTOs\Report\UpdateMaintenanceReportStatusDTO;
use App\Enums\Report\Maintenance\AssetMaintenanceReportStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Report\CreateMaintenanceReportRequest;
use App\Http\Requests\Report\UpdateMaintenanceReportRequest;
use App\Http\Requests\Report\UpdateMaintenanceReportStatusRequest;
use App\Services\Report\MaintenanceReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MaintenanceReportController extends Controller
{
    public function __construct(
        private MaintenanceReportService $service
    ) {}

    public function index(Request $request)
    {
        $reports = $this->service->getLogs($request->keyword, ($request->status) ? AssetMaintenanceReportStatus::from($request->status) : null, $request->page);
        return view('maintenance-report.index', [
            'reports' => $reports
        ]);
    }

    public function show(string $id)
    {
        try 
        {
            $report = $this->service->getLog($id);
            return response()->json([
                'message' => 'Laporan berhasil ditemukan',
                'data' => $report
            ]);
        }
        catch(\Throwable $e)
        {
            return response()->json([
                'message' => $e->getMessage()
            ], $e->getCode() ? $e->getCode() : 500);
        }
    }

    public function store(CreateMaintenanceReportRequest $request)
    {
        try 
        {
            $this->service->createLog(CreateMaintenanceReportDTO::fromArray($request->validated()));

            session()->flash('success', 'Laporan berhasil dikirim');
            return response()->json(['success' => true]);
        }
        catch(\Throwable $e) 
        {
            return response()->json([
                'message' => $e->getMessage()
            ], $e->getCode() ? $e->getCode() : 500);
        }
    }

    public function update(UpdateMaintenanceReportRequest $request)
    {
        try 
        {
            $this->service->updateLog(UpdateMaintenanceReportDTO::fromArray($request->validated()));

            session()->flash('success', 'Laporan berhasil diperbaharui');
            return response()->json(['success' => true]);
        }
        catch(\Throwable $e)
        {
            return response()->json([
                'message' => $e->getMessage()
            ], $e->getCode() ? $e->getCode() : 500);
        }
    }

    public function updateStatus(UpdateMaintenanceReportStatusRequest $request)
    {
        try 
        {
            $this->service->updateStatus(UpdateMaintenanceReportStatusDTO::fromArray($request->validated()));

            session()->flash('success', 'Status laporan berhasil diperbaharui');
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
            $this->service->deleteLog($id);

            session()->flash('success', 'Laporan berhasil dihapus');
            return response()->json(['success' => true]);
        }
        catch(\Throwable $e)
        {
            return response()->json([
                'message' => $e->getMessage()
            ], $e->getCode() ? $e->getCode() : 500);
        }
    }
}
