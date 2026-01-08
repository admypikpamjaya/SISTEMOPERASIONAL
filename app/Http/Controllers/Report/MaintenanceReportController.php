<?php

namespace App\Http\Controllers\Report;

use App\DTOs\Report\CreateMaintenanceReportDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Report\CreateMaintenanceReportRequest;
use App\Services\Report\MaintenanceReportService;
use Illuminate\Http\Request;

class MaintenanceReportController extends Controller
{
    public function __construct(
        private MaintenanceReportService $service
    ) {}

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
}
