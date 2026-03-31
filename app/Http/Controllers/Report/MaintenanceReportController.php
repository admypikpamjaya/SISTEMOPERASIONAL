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
use Symfony\Component\HttpFoundation\StreamedResponse;

class MaintenanceReportController extends Controller
{
    public function __construct(
        private MaintenanceReportService $service
    ) {}

    public function index(Request $request)
    {
        // $reports = $this->service->getLogs($request->keyword, ($request->status) ? AssetMaintenanceReportStatus::from($request->status) : null, $request->page);
        // return view('maintenance-report.index', [
        //     'reports' => $reports
        // ]);

        $request->validate([
            'keyword'   => ['nullable', 'string'],

            'status' => [
                'nullable',
                'in:' . implode(',', array_column(AssetMaintenanceReportStatus::cases(), 'value'))
            ],

            'date_from' => ['nullable', 'date'],
            'date_to'   => ['nullable', 'date', 'after_or_equal:date_from'],
        ], [
            'date_from.date' => 'Tanggal mulai tidak valid.',
            'date_to.date' => 'Tanggal akhir tidak valid.',
            'date_to.after_or_equal' => 'Tanggal akhir harus lebih besar atau sama dengan tanggal mulai.',

            'status.in' => 'Status yang dipilih tidak valid.',
        ]);

        $reports = $this->service->getLogs(
            $request->keyword,
            $request->status 
                ? AssetMaintenanceReportStatus::from($request->status) 
                : null,
            $request->page ?? 1,
            $request->date_from,
            $request->date_to
        );

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

    public function exportExcel(Request $request)
    {
        try 
        {
            $ids = $request->input('ids', []);
            if(count($ids) === 0)
                return redirect()->route('maintenance-report.index')->with('error', 'Tidak ada data yang dipilih');

            $file = $this->service->exportLogToExcel($ids);

            $response = new StreamedResponse(function() use ($file) {
                $file->save('php://output');
            });
                        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            $response->headers->set('Content-Disposition', 'attachment;filename="maintenance_logs.xlsx"');
            $response->headers->set('Cache-Control', 'max-age=0');

            return $response;
        }
        catch(\Throwable $e)
        {
            return redirect()->route('maintenance-report.index')->with('error', $e->getMessage());
        }
    }
}
