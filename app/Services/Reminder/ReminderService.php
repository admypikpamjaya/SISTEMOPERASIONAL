<?php

namespace App\Services\Reminder;

use App\Models\Billing;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ReminderService
{
    /**
     * Ambil billing PENDING yang sudah jatuh tempo
     */
    public function getDueReminders(Carbon $date): Collection
    {
        return Billing::with('parent')
            ->where('status', 'PENDING')
            ->whereDate('due_date', '<=', $date)
            ->get();
    }

    /**
     * Bangun payload reminder (belum kirim)
     */
    public function buildReminderMessage(Billing $billing): array
    {
        $parent = $billing->parent;

        return [
            'parent_name' => $parent->name,
            'amount' => $billing->amount,
            'due_date' => $billing->due_date->format('d M Y'),
            'message' => "Yth. {$parent->name}, tagihan sebesar Rp{$billing->amount} telah jatuh tempo.",
        ];
    }
}
