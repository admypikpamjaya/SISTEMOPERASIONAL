<?php

namespace App\Repositories;

use App\Models\Payment;

class PaymentRepository
{
    public function create(array $data): Payment
    {
        return Payment::create($data);
    }

    public function getByBilling(int $billingId)
    {
        return Payment::where('billing_id', $billingId)->get();
    }

    public function updateStatus(int $paymentId, string $status): bool
    {
        return Payment::where('id', $paymentId)
            ->update(['status' => $status]);
    }
}
