<?php

namespace App\Services\Finance;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FinanceCategoryScopeService
{
    /**
     * @return array<int, string>
     */
    public function idsFor(?string $categoryId): array
    {
        $categoryId = trim((string) $categoryId);
        if ($categoryId === '') {
            return [];
        }

        if (!Schema::hasTable('finance_categories')) {
            return [$categoryId];
        }

        if (!Schema::hasTable('finance_category_members')) {
            return [$categoryId];
        }

        $ids = [];
        $queue = [$categoryId];

        while (!empty($queue)) {
            $currentId = (string) array_shift($queue);
            if ($currentId === '' || isset($ids[$currentId])) {
                continue;
            }

            $ids[$currentId] = $currentId;

            $memberIds = DB::table('finance_category_members')
                ->where('parent_category_id', $currentId)
                ->pluck('member_category_id')
                ->map(static fn ($id): string => (string) $id)
                ->all();

            foreach ($memberIds as $memberId) {
                if (!isset($ids[$memberId])) {
                    $queue[] = $memberId;
                }
            }
        }

        return array_values($ids);
    }

    public function apply($query, string $column, ?string $categoryId)
    {
        $categoryIds = $this->idsFor($categoryId);
        if (empty($categoryIds)) {
            return $query;
        }

        return $query->whereIn($column, $categoryIds);
    }

}
