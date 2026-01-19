<?php

namespace App\Repositories;

use App\Models\Billing;

class BillingRepository
{
    public function findById(int $id): ?Billing
    {
        return Billing::with('payments')->find($id);
    }

    public function create(array $data): Billing
    {
        return Billing::create($data);
    }

    public function getUnpaidByParent(int $parentId)
    {
        return Billing::where('parent_id', $parentId)
            ->where('status', 'unpaid')
            ->get();
    }
}
