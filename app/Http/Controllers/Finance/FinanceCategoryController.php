<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\FinanceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        $query = FinanceCategory::query()
            ->with('creator:id,name')
            ->orderByRaw("CASE WHEN status = 'active' THEN 0 ELSE 1 END")
            ->orderBy('name');

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

        $usageCounts = $categories
            ->getCollection()
            ->mapWithKeys(fn (FinanceCategory $category): array => [
                $category->id => $this->countUsage((string) $category->id),
            ]);

        return view('finance.categories.index', [
            'categories' => $categories,
            'editCategory' => $editCategory,
            'usageCounts' => $usageCounts,
            'filters' => [
                'q' => $validated['q'] ?? null,
                'status' => $status,
            ],
            'statusOptions' => FinanceCategory::statusOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validatePayload($request);

        try {
            FinanceCategory::query()->create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'status' => $validated['status'],
                'created_by' => auth()->id() ? (string) auth()->id() : null,
            ]);

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
            $category->update([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'status' => $validated['status'],
            ]);

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
     * @return array{name:string,description:?string,status:string}
     */
    private function validatePayload(Request $request, ?FinanceCategory $category = null): array
    {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:120',
                Rule::unique('finance_categories', 'name')->ignore($category?->id),
            ],
            'description' => 'nullable|string|max:5000',
            'status' => ['required', Rule::in(array_keys(FinanceCategory::statusOptions()))],
        ], [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.unique' => 'Nama kategori sudah digunakan.',
            'status.required' => 'Status kategori wajib dipilih.',
            'status.in' => 'Status kategori tidak valid.',
        ]);
    }

    private function countUsage(string $categoryId): int
    {
        return collect(self::USAGE_TABLES)
            ->filter(fn (string $table): bool => DB::getSchemaBuilder()->hasTable($table)
                && DB::getSchemaBuilder()->hasColumn($table, 'category_id'))
            ->sum(fn (string $table): int => (int) DB::table($table)->where('category_id', $categoryId)->count());
    }
}
