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

    /**
     * @return array{
     *     selected_id:?string,
     *     selected_name:string,
     *     scope_ids:array<int, string>,
     *     scope_names:array<int, string>,
     *     scope_label:string,
     *     is_group:bool
     * }
     */
    public function describe(?string $categoryId): array
    {
        $categoryId = trim((string) $categoryId);
        $allCategoriesLabel = (string) __('app.finance.all_categories_short');

        if ($categoryId === '') {
            return [
                'selected_id' => null,
                'selected_name' => $allCategoriesLabel,
                'scope_ids' => [],
                'scope_names' => [$allCategoriesLabel],
                'scope_label' => $allCategoriesLabel,
                'is_group' => false,
            ];
        }

        if (!Schema::hasTable('finance_categories')) {
            return [
                'selected_id' => $categoryId,
                'selected_name' => $categoryId,
                'scope_ids' => [$categoryId],
                'scope_names' => [$categoryId],
                'scope_label' => $categoryId,
                'is_group' => false,
            ];
        }

        $category = DB::table('finance_categories')
            ->where('id', $categoryId)
            ->first(['id', 'name', 'category_type']);

        if ($category === null) {
            return [
                'selected_id' => $categoryId,
                'selected_name' => (string) __('app.finance.unknown_category'),
                'scope_ids' => [$categoryId],
                'scope_names' => [(string) __('app.finance.unknown_category')],
                'scope_label' => (string) __('app.finance.unknown_category'),
                'is_group' => false,
            ];
        }

        $scopeIds = $this->idsFor($categoryId);
        $namesById = DB::table('finance_categories')
            ->whereIn('id', $scopeIds)
            ->pluck('name', 'id')
            ->mapWithKeys(static fn ($name, $id): array => [(string) $id => (string) $name])
            ->all();

        $scopeNames = array_values(array_filter(array_map(
            static fn (string $id): ?string => $namesById[$id] ?? null,
            $scopeIds
        )));

        if (empty($scopeNames)) {
            $scopeNames = [(string) $category->name];
        }

        return [
            'selected_id' => (string) $category->id,
            'selected_name' => (string) $category->name,
            'scope_ids' => $scopeIds,
            'scope_names' => $scopeNames,
            'scope_label' => implode(', ', $scopeNames),
            'is_group' => (string) ($category->category_type ?? '') === 'group' || count($scopeNames) > 1,
        ];
    }

}
