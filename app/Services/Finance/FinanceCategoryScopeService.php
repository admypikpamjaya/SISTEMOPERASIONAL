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

        $ids = [$categoryId];

        if (!Schema::hasTable('finance_category_members')) {
            return $ids;
        }

        $this->collectMemberIds($categoryId, $ids, []);

        return array_values(array_unique(array_filter($ids)));
    }

    public function apply($query, string $column, ?string $categoryId)
    {
        $categoryIds = $this->idsFor($categoryId);
        if (empty($categoryIds)) {
            return $query;
        }

        return $query->whereIn($column, $categoryIds);
    }

    /**
     * @param array<int, string> $ids
     * @param array<string, bool> $visited
     */
    private function collectMemberIds(string $parentId, array &$ids, array $visited): void
    {
        if (($visited[$parentId] ?? false) === true) {
            return;
        }

        $visited[$parentId] = true;

        $memberIds = DB::table('finance_category_members')
            ->where('parent_category_id', $parentId)
            ->pluck('member_category_id')
            ->map(static fn ($id): string => (string) $id)
            ->all();

        foreach ($memberIds as $memberId) {
            $ids[] = $memberId;
            $this->collectMemberIds($memberId, $ids, $visited);
        }
    }
}
