<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\FinanceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Throwable;

class FinanceCategoryController extends Controller
{
    private const USAGE_TABLES = [
        'finance_accounts',
        'finance_invoices',
        'finance_statement_batches',
        'finance_statement_rows',
        'finance_general_ledger_batches',
        'finance_general_ledger_entries',
        'finance_asset_policies',
        'finance_depreciation_runs',
        'finance_depreciation_histories',
        'finance_depreciation_calculation_logs',
        'finance_reconciliation_snapshots',
        'finance_report_snapshots',
    ];

    public function index(Request $request)
    {
        $validated = $request->validate([
            'q' => 'nullable|string|max:120',
            'status' => ['nullable', Rule::in(['all', ...array_keys(FinanceCategory::statusOptions())])],
            'edit' => 'nullable|uuid',
        ]);

        $relations = ['creator:id,name'];
        $categoryColumns = $this->categorySelectColumns();
        if (Schema::hasTable('finance_category_members')) {
            $relations[] = 'members:' . implode(',', $categoryColumns);
        }

        $query = FinanceCategory::query()
            ->with($relations)
            ->orderByRaw("CASE WHEN status = 'active' THEN 0 ELSE 1 END");

        if (Schema::hasColumn('finance_categories', 'sort_order')) {
            $query->orderBy('sort_order');
        }

        $query->orderBy('name');

        if (!empty($validated['q'])) {
            $keyword = trim((string) $validated['q']);
            $query->where(function ($builder) use ($keyword): void {
                $builder
                    ->where('name', 'like', '%' . $keyword . '%')
                    ->orWhere('description', 'like', '%' . $keyword . '%');
            });
        }

        $status = $validated['status'] ?? 'all';
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $categories = $query
            ->paginate(15)
            ->withQueryString();

        $editCategory = !empty($validated['edit'])
            ? FinanceCategory::query()->find($validated['edit'])
            : null;

        if ($editCategory !== null && Schema::hasTable('finance_category_members')) {
            $editCategory->load('members:' . implode(',', $categoryColumns));
        }

        $usageCounts = $categories
            ->getCollection()
            ->mapWithKeys(fn (FinanceCategory $category): array => [
                $category->id => $this->countUsage((string) $category->id),
            ]);

        $memberOptionsQuery = FinanceCategory::query()
            ->orderByRaw("CASE WHEN status = 'active' THEN 0 ELSE 1 END");

        if (Schema::hasColumn('finance_categories', 'sort_order')) {
            $memberOptionsQuery->orderBy('sort_order');
        }

        $memberOptions = $memberOptionsQuery
            ->orderBy('name')
            ->get($categoryColumns);

        return view('finance.categories.index', [
            'categories' => $categories,
            'editCategory' => $editCategory,
            'memberOptions' => $memberOptions,
            'usageCounts' => $usageCounts,
            'filters' => [
                'q' => $validated['q'] ?? null,
                'status' => $status,
            ],
            'statusOptions' => FinanceCategory::statusOptions(),
            'typeOptions' => FinanceCategory::typeOptions(),
            'sourceOptions' => FinanceCategory::sourceOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validatePayload($request);

        try {
            DB::transaction(function () use ($validated): void {
                $category = FinanceCategory::query()->create($this->categorySavePayload($validated, true));

                $this->syncMembers($category, $validated['category_type'], $validated['member_ids'] ?? []);
            });

            return redirect()
                ->route('finance.categories.index')
                ->with('success', __('app.finance_categories.created_success'));
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', __('app.finance_categories.created_failed'));
        }
    }

    public function update(Request $request, FinanceCategory $category)
    {
        $validated = $this->validatePayload($request, $category);

        try {
            DB::transaction(function () use ($category, $validated): void {
                $category->update($this->categorySavePayload($validated));

                $this->syncMembers($category, $validated['category_type'], $validated['member_ids'] ?? []);
            });

            return redirect()
                ->route('finance.categories.index')
                ->with('success', __('app.finance_categories.updated_success'));
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', __('app.finance_categories.updated_failed'));
        }
    }

    public function visibility(Request $request, FinanceCategory $category)
    {
        $validated = $request->validate([
            'visible' => ['required', 'boolean'],
        ]);

        $visible = (bool) $validated['visible'];

        try {
            $category->update([
                'status' => $visible
                    ? FinanceCategory::STATUS_ACTIVE
                    : FinanceCategory::STATUS_INACTIVE,
            ]);

            return redirect()
                ->back()
                ->with(
                    'success',
                    $visible
                        ? __('app.finance_categories.visible_success')
                        : __('app.finance_categories.hidden_success')
                );
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->back()
                ->with('error', __('app.finance_categories.visibility_failed'));
        }
    }

    public function destroy(FinanceCategory $category)
    {
        try {
            $usageCount = $this->countUsage((string) $category->id);
            if ($usageCount > 0) {
                return redirect()
                    ->back()
                    ->with('error', __('app.finance_categories.delete_used_error'));
            }

            DB::transaction(function () use ($category): void {
                if (Schema::hasTable('finance_category_members')) {
                    DB::table('finance_category_members')
                        ->where('parent_category_id', $category->id)
                        ->orWhere('member_category_id', $category->id)
                        ->delete();
                }

                $category->delete();
            });

            return redirect()
                ->route('finance.categories.index')
                ->with('success', __('app.finance_categories.deleted_success'));
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->back()
                ->with('error', __('app.finance_categories.deleted_failed'));
        }
    }

    /**
     * @return array{name:string,description:?string,status:string,category_type:string,source_type:string,member_ids?:array<int, string>}
     */
    private function validatePayload(Request $request, ?FinanceCategory $category = null): array
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:120',
                Rule::unique('finance_categories', 'name')->ignore($category?->id),
            ],
            'description' => 'nullable|string|max:5000',
            'status' => ['required', Rule::in(array_keys(FinanceCategory::statusOptions()))],
            'category_type' => ['required', Rule::in(array_keys(FinanceCategory::typeOptions()))],
            'source_type' => ['required', Rule::in(array_keys(FinanceCategory::sourceOptions()))],
            'member_ids' => [
                Rule::requiredIf($request->input('category_type') === FinanceCategory::TYPE_GROUP),
                'array',
                'min:' . ($request->input('category_type') === FinanceCategory::TYPE_GROUP ? '1' : '0'),
            ],
            'member_ids.*' => ['uuid', 'distinct', Rule::exists('finance_categories', 'id')],
        ], [
            'name.required' => __('app.finance_categories.validation.name_required'),
            'name.unique' => __('app.finance_categories.validation.name_unique'),
            'status.required' => __('app.finance_categories.validation.visibility_required'),
            'status.in' => __('app.finance_categories.validation.visibility_invalid'),
            'category_type.required' => __('app.finance_categories.validation.type_required'),
            'category_type.in' => __('app.finance_categories.validation.type_invalid'),
            'source_type.required' => __('app.finance_categories.validation.source_required'),
            'source_type.in' => __('app.finance_categories.validation.source_invalid'),
            'member_ids.required' => __('app.finance_categories.validation.members_required'),
            'member_ids.min' => __('app.finance_categories.validation.members_required'),
        ]);

        $validated['category_type'] = $validated['category_type'] ?? FinanceCategory::TYPE_SINGLE;
        $validated['source_type'] = $validated['source_type'] ?? FinanceCategory::SOURCE_CUSTOM;
        $validated['member_ids'] = array_values(array_unique(array_filter(
            $validated['member_ids'] ?? [],
            static fn ($id): bool => is_string($id) && trim($id) !== ''
        )));

        if ($category !== null && in_array((string) $category->id, $validated['member_ids'], true)) {
            throw ValidationException::withMessages([
                'member_ids' => __('app.finance_categories.validation.self_member'),
            ]);
        }

        if ($category !== null && $validated['category_type'] === FinanceCategory::TYPE_GROUP) {
            foreach ($validated['member_ids'] as $memberId) {
                if ($this->wouldCreateCycle((string) $category->id, (string) $memberId)) {
                    throw ValidationException::withMessages([
                        'member_ids' => __('app.finance_categories.validation.member_cycle'),
                    ]);
                }
            }
        }

        return $validated;
    }

    /**
     * @param array{name:string,description:?string,status:string,category_type:string,source_type:string} $validated
     * @return array<string, mixed>
     */
    private function categorySavePayload(array $validated, bool $includeCreator = false): array
    {
        $payload = [
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'],
        ];

        if (Schema::hasColumn('finance_categories', 'category_type')) {
            $payload['category_type'] = $validated['category_type'];
        }

        if (Schema::hasColumn('finance_categories', 'source_type')) {
            $payload['source_type'] = $validated['source_type'];
        }

        if ($includeCreator && Schema::hasColumn('finance_categories', 'created_by')) {
            $payload['created_by'] = auth()->id() ? (string) auth()->id() : null;
        }

        return $payload;
    }

    private function countUsage(string $categoryId): int
    {
        return collect(self::USAGE_TABLES)
            ->filter(fn (string $table): bool => DB::getSchemaBuilder()->hasTable($table)
                && DB::getSchemaBuilder()->hasColumn($table, 'category_id'))
            ->sum(fn (string $table): int => (int) DB::table($table)->where('category_id', $categoryId)->count());
    }

    /**
     * @return array<int, string>
     */
    private function categorySelectColumns(): array
    {
        $columns = ['id', 'name', 'status'];
        foreach (['category_type', 'source_type', 'sort_order'] as $column) {
            if (Schema::hasColumn('finance_categories', $column)) {
                $columns[] = $column;
            }
        }

        return $columns;
    }

    /**
     * @param array<int, string> $memberIds
     */
    private function syncMembers(FinanceCategory $category, string $type, array $memberIds): void
    {
        if (!Schema::hasTable('finance_category_members')) {
            return;
        }

        if ($type !== FinanceCategory::TYPE_GROUP) {
            $category->members()->sync([]);

            return;
        }

        $category->members()->sync(array_values(array_filter(
            $memberIds,
            static fn (string $memberId): bool => $memberId !== (string) $category->id
        )));
    }

    private function wouldCreateCycle(string $categoryId, string $memberId): bool
    {
        if (!Schema::hasTable('finance_category_members')) {
            return false;
        }

        $visited = [];
        $stack = [$memberId];

        while (!empty($stack)) {
            $currentId = array_pop($stack);
            if ($currentId === $categoryId) {
                return true;
            }

            if (($visited[$currentId] ?? false) === true) {
                continue;
            }

            $visited[$currentId] = true;
            $children = DB::table('finance_category_members')
                ->where('parent_category_id', $currentId)
                ->pluck('member_category_id')
                ->map(static fn ($id): string => (string) $id)
                ->all();

            array_push($stack, ...$children);
        }

        return false;
    }
}
