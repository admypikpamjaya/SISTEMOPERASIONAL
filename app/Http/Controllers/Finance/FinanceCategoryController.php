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

        $memberOptions = FinanceCategory::query()
            ->active()
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
                $category = FinanceCategory::query()->create([
                    'name' => $validated['name'],
                    'description' => $validated['description'] ?? null,
                    'status' => $validated['status'],
                    'category_type' => $validated['category_type'],
                    'source_type' => $validated['source_type'],
                    'created_by' => auth()->id() ? (string) auth()->id() : null,
                ]);

                $this->syncMembers($category, $validated['category_type'], $validated['member_ids'] ?? []);
            });

            return redirect()
                ->route('finance.categories.index')
                ->with('success', 'Kategori finance berhasil ditambahkan.');
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan kategori finance.');
        }
    }

    public function update(Request $request, FinanceCategory $category)
    {
        $validated = $this->validatePayload($request, $category);

        try {
            DB::transaction(function () use ($category, $validated): void {
                $category->update([
                    'name' => $validated['name'],
                    'description' => $validated['description'] ?? null,
                    'status' => $validated['status'],
                    'category_type' => $validated['category_type'],
                    'source_type' => $validated['source_type'],
                ]);

                $this->syncMembers($category, $validated['category_type'], $validated['member_ids'] ?? []);
            });

            return redirect()
                ->route('finance.categories.index')
                ->with('success', 'Kategori finance berhasil diperbarui.');
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui kategori finance.');
        }
    }

    public function destroy(FinanceCategory $category)
    {
        try {
            $usageCount = $this->countUsage((string) $category->id);
            if ($usageCount > 0) {
                $category->update(['status' => FinanceCategory::STATUS_INACTIVE]);

                return redirect()
                    ->route('finance.categories.index')
                    ->with('success', 'Kategori masih dipakai oleh data finance, jadi dinonaktifkan agar data lama tetap aman.');
            }

            $category->delete();

            return redirect()
                ->route('finance.categories.index')
                ->with('success', 'Kategori finance berhasil dihapus.');
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->back()
                ->with('error', 'Gagal menghapus kategori finance.');
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
            'name.required' => 'Nama kategori wajib diisi.',
            'name.unique' => 'Nama kategori sudah digunakan.',
            'status.required' => 'Status kategori wajib dipilih.',
            'status.in' => 'Status kategori tidak valid.',
            'category_type.required' => 'Tipe kategori wajib dipilih.',
            'category_type.in' => 'Tipe kategori tidak valid.',
            'source_type.required' => 'Sumber kategori wajib dipilih.',
            'source_type.in' => 'Sumber kategori tidak valid.',
            'member_ids.required' => 'Kategori gabungan wajib memiliki minimal satu anggota.',
            'member_ids.min' => 'Kategori gabungan wajib memiliki minimal satu anggota.',
        ]);

        $validated['category_type'] = $validated['category_type'] ?? FinanceCategory::TYPE_SINGLE;
        $validated['source_type'] = $validated['source_type'] ?? FinanceCategory::SOURCE_CUSTOM;
        $validated['member_ids'] = array_values(array_unique(array_filter(
            $validated['member_ids'] ?? [],
            static fn ($id): bool => is_string($id) && trim($id) !== ''
        )));

        if ($category !== null && in_array((string) $category->id, $validated['member_ids'], true)) {
            throw ValidationException::withMessages([
                'member_ids' => 'Kategori tidak boleh menjadi anggota dari dirinya sendiri.',
            ]);
        }

        if ($category !== null && $validated['category_type'] === FinanceCategory::TYPE_GROUP) {
            foreach ($validated['member_ids'] as $memberId) {
                if ($this->wouldCreateCycle((string) $category->id, (string) $memberId)) {
                    throw ValidationException::withMessages([
                        'member_ids' => 'Relasi gabungan kategori tidak boleh membentuk putaran.',
                    ]);
                }
            }
        }

        return $validated;
    }

    private function countUsage(string $categoryId): int
    {
        $dataUsage = collect(self::USAGE_TABLES)
            ->filter(fn (string $table): bool => DB::getSchemaBuilder()->hasTable($table)
                && DB::getSchemaBuilder()->hasColumn($table, 'category_id'))
            ->sum(fn (string $table): int => (int) DB::table($table)->where('category_id', $categoryId)->count());

        $memberUsage = 0;
        if (DB::getSchemaBuilder()->hasTable('finance_category_members')) {
            $memberUsage = (int) DB::table('finance_category_members')
                ->where('parent_category_id', $categoryId)
                ->orWhere('member_category_id', $categoryId)
                ->count();
        }

        return $dataUsage + $memberUsage;
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
