<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Http\Requests\Report\StoreMaintenanceNotificationRecipientRequest;
use App\Models\MaintenanceNotificationRecipient;
use App\Services\Report\MaintenanceNotificationService;
use Illuminate\Http\JsonResponse;

class MaintenanceNotificationRecipientController extends Controller
{
    public function __construct(
        private MaintenanceNotificationService $service
    ) {}

    public function index(): JsonResponse
    {
        return response()->json([
            'data' => $this->service->getRecipientPayload(),
        ]);
    }

    public function store(StoreMaintenanceNotificationRecipientRequest $request): JsonResponse
    {
        try {
            $recipient = $this->service->createAdditionalRecipient(
                $request->validated(),
                auth()->id()
            );

            $label = $recipient->name
                ? $recipient->name . ' (' . $recipient->email . ')'
                : $recipient->email;

            return response()->json([
                'message' => 'Email maintenance ' . $label . ' berhasil ditambahkan.',
                'data' => $this->service->getRecipientPayload(),
            ], 201);
        } catch (\Throwable $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], $exception->getCode() ?: 500);
        }
    }

    public function destroy(MaintenanceNotificationRecipient $recipient): JsonResponse
    {
        try {
            $label = $recipient->name
                ? $recipient->name . ' (' . $recipient->email . ')'
                : $recipient->email;

            $this->service->deleteAdditionalRecipient($recipient);

            return response()->json([
                'message' => 'Email maintenance ' . $label . ' berhasil dihapus.',
                'data' => $this->service->getRecipientPayload(),
            ]);
        } catch (\Throwable $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], $exception->getCode() ?: 500);
        }
    }
}
