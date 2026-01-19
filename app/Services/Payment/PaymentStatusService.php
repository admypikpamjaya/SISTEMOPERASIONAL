<?php

namespace App\Services\Payment;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PaymentStatusService
{
    /**
     * Manual confirmation by admin / superadmin
     */
    public function confirmManual(Payment $payment, User $admin): Payment
    {
        if ($payment->status === 'confirmed') {
            throw new RuntimeException('Payment already confirmed');
        }

        return DB::transaction(function () use ($payment, $admin) {

            $payment->update([
                'status' => 'confirmed',
                'confirmed_by' => $admin->id,
                'confirmed_at' => now(),
            ]);

            // update billing status
            $billing = $payment->billing;
            $billing->update([
                'status' => 'paid',
            ]);

            return $payment->fresh(['billing']);
        });
    }

    /**
     * Reject manual payment
     */
    public function reject(Payment $payment, User $admin, ?string $reason = null): Payment
    {
        if ($payment->status !== 'pending') {
            throw new RuntimeException('Only pending payments can be rejected');
        }

        $payment->update([
            'status' => 'rejected',
            'confirmed_by' => $admin->id,
            'notes' => $reason,
        ]);

        return $payment->fresh();
    }
}
