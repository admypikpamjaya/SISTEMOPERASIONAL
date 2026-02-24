<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\FinanceAccountStoreRequest;
use App\Http\Requests\Finance\FinanceAccountUpdateRequest;
use App\Models\FinanceAccount;
use App\Models\FinanceAccountLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class FinanceAccountController extends Controller
{
    /**
     * @var array<int>
     */
    private const CORE_CLASSIFICATIONS = [1, 2, 3, 4, 5, 8, 9];

    public function index(Request $request)
    {
        $validated = $request->validate([
            'group' => 'nullable|integer|between:1,255',
            'edit' => 'nullable|uuid',
        ]);

        $selectedGroup = isset($validated['group']) ? (int) $validated['group'] : null;

        $query = FinanceAccount::query()
            ->orderBy('class_no')
            ->orderBy('code');

        if ($selectedGroup !== null) {
            $query->where('class_no', $selectedGroup);
        }

        $accounts = $query
            ->paginate(15)
            ->withQueryString();

        $accountCounts = FinanceAccount::query()
            ->selectRaw('class_no, COUNT(*) as total')
            ->groupBy('class_no')
            ->pluck('total', 'class_no');

        $existingClasses = FinanceAccount::query()
            ->select('class_no')
            ->distinct()
            ->orderBy('class_no')
            ->pluck('class_no')
            ->map(fn ($classNo) => (int) $classNo)
            ->values();

        $coreClasses = collect(self::CORE_CLASSIFICATIONS);

        $groupOrder = $coreClasses
            ->merge($existingClasses)
            ->when($selectedGroup !== null, fn ($collection) => $collection->push((int) $selectedGroup))
            ->unique()
            ->sort()
            ->values()
            ->all();

        $classNoSuggestions = $coreClasses
            ->merge($existingClasses)
            ->when($selectedGroup !== null, fn ($collection) => $collection->push((int) $selectedGroup))
            ->unique()
            ->sort()
            ->values()
            ->all();

        $groupLabels = $this->resolveGroupLabels($groupOrder);

        $typeSuggestions = collect(array_keys(FinanceAccount::TYPE_LABELS))
            ->merge(
                FinanceAccount::query()
                    ->select('type')
                    ->distinct()
                    ->orderBy('type')
                    ->pluck('type')
            )
            ->map(fn ($type) => strtoupper(trim((string) $type)))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $editAccount = null;
        if (!empty($validated['edit'])) {
            $editAccount = FinanceAccount::query()->find($validated['edit']);
            if ($editAccount !== null && $selectedGroup === null) {
                $selectedGroup = (int) $editAccount->class_no;
            }
        }

        $accountLogs = collect();
        if (Schema::hasTable('finance_account_logs')) {
            $accountLogs = FinanceAccountLog::query()
                ->with([
                    'account:id,code,name,class_no',
                    'actor:id,name',
                ])
                ->latest('id')
                ->limit(50)
                ->get();
        }

        return view('finance.accounts.index', [
            'accounts' => $accounts,
            'selectedGroup' => $selectedGroup,
            'groupOrder' => $groupOrder,
            'groupLabels' => $groupLabels,
            'classNoSuggestions' => $classNoSuggestions,
            'coreClassifications' => self::CORE_CLASSIFICATIONS,
            'typeLabels' => FinanceAccount::TYPE_LABELS,
            'typeClassMap' => FinanceAccount::TYPE_CLASS_MAP,
            'typeSuggestions' => $typeSuggestions,
            'accountCounts' => $accountCounts,
            'editAccount' => $editAccount,
            'accountLogs' => $accountLogs,
        ]);
    }

    public function store(FinanceAccountStoreRequest $request)
    {
        try {
            $validated = $request->validated();
            $type = (string) $validated['type'];
            $classNo = (int) $validated['class_no'];
            $actorId = auth()->id() ? (string) auth()->id() : null;

            $account = FinanceAccount::query()->create([
                'code' => (string) $validated['code'],
                'name' => (string) $validated['name'],
                'type' => $type,
                'class_no' => $classNo,
                'is_active' => (bool) $validated['is_active'],
                'created_by' => $actorId,
                'updated_by' => $actorId,
            ]);

            $this->writeAccountLog(
                account: $account,
                action: FinanceAccountLog::ACTION_CREATED,
                actorId: $actorId,
                beforeData: null,
                afterData: $this->serializeAccount($account)
            );

            return redirect()
                ->route('finance.accounts.index', ['group' => $classNo])
                ->with('success', 'Akun berhasil ditambahkan ke bagan akun.');
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan akun.');
        }
    }

    public function update(FinanceAccountUpdateRequest $request, FinanceAccount $account)
    {
        try {
            $validated = $request->validated();
            $type = (string) $validated['type'];
            $classNo = (int) $validated['class_no'];
            $actorId = auth()->id() ? (string) auth()->id() : null;
            $beforeData = $this->serializeAccount($account);

            $account->update([
                'code' => (string) $validated['code'],
                'name' => (string) $validated['name'],
                'type' => $type,
                'class_no' => $classNo,
                'is_active' => (bool) $validated['is_active'],
                'updated_by' => $actorId,
            ]);

            $account->refresh();

            $this->writeAccountLog(
                account: $account,
                action: FinanceAccountLog::ACTION_UPDATED,
                actorId: $actorId,
                beforeData: $beforeData,
                afterData: $this->serializeAccount($account)
            );

            return redirect()
                ->route('finance.accounts.index', ['group' => $classNo])
                ->with('success', 'Akun berhasil diperbarui.');
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui akun.');
        }
    }

    public function destroyClassification(int $classNo)
    {
        try {
            if ($classNo < 1 || $classNo > 255) {
                return redirect()
                    ->route('finance.accounts.index')
                    ->with('error', 'No klasifikasi kiri tidak valid.');
            }

            if (in_array($classNo, self::CORE_CLASSIFICATIONS, true)) {
                return redirect()
                    ->route('finance.accounts.index', ['group' => $classNo])
                    ->with('error', 'Klasifikasi bawaan tidak dapat dihapus.');
            }

            $actorId = auth()->id() ? (string) auth()->id() : null;
            $accounts = FinanceAccount::query()
                ->where('class_no', $classNo)
                ->orderBy('code')
                ->get();

            if ($accounts->isEmpty()) {
                return redirect()
                    ->route('finance.accounts.index')
                    ->with('success', 'Klasifikasi kiri sudah kosong.');
            }

            DB::transaction(function () use ($accounts, $actorId): void {
                foreach ($accounts as $account) {
                    $beforeData = $this->serializeAccount($account);
                    $this->writeAccountLog(
                        account: $account,
                        action: FinanceAccountLog::ACTION_DELETED,
                        actorId: $actorId,
                        beforeData: $beforeData,
                        afterData: null
                    );

                    $account->delete();
                }
            });

            return redirect()
                ->route('finance.accounts.index')
                ->with('success', 'Klasifikasi kiri berhasil dihapus.');
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('finance.accounts.index', ['group' => $classNo])
                ->with('error', 'Gagal menghapus klasifikasi kiri.');
        }
    }

    private function writeAccountLog(
        FinanceAccount $account,
        string $action,
        ?string $actorId,
        ?array $beforeData,
        ?array $afterData
    ): void {
        if (!Schema::hasTable('finance_account_logs')) {
            return;
        }

        FinanceAccountLog::query()->create([
            'finance_account_id' => (string) $account->id,
            'action' => $action,
            'before_data' => $beforeData,
            'after_data' => $afterData,
            'actor_id' => $actorId,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeAccount(FinanceAccount $account): array
    {
        return [
            'id' => (string) $account->id,
            'code' => (string) $account->code,
            'name' => (string) $account->name,
            'type' => (string) $account->type,
            'type_label' => (string) $account->type_label,
            'class_no' => (int) $account->class_no,
            'is_active' => (bool) $account->is_active,
        ];
    }

    private function resolveGroupLabels(array $groupOrder): array
    {
        $labels = FinanceAccount::CLASS_LABELS;
        if (empty($groupOrder)) {
            return $labels;
        }

        $typesByClass = FinanceAccount::query()
            ->select(['class_no', 'type', 'created_at'])
            ->whereIn('class_no', $groupOrder)
            ->orderBy('created_at')
            ->get()
            ->groupBy('class_no');

        foreach ($groupOrder as $groupNo) {
            $groupNo = (int) $groupNo;
            if (isset($labels[$groupNo])) {
                continue;
            }

            $typeLabels = collect($typesByClass->get($groupNo, collect()))
                ->pluck('type')
                ->map(fn ($type) => $this->resolveTypeLabel((string) $type))
                ->filter()
                ->unique()
                ->values();

            if ($typeLabels->isEmpty()) {
                $labels[$groupNo] = 'Klasifikasi ' . $groupNo;
                continue;
            }

            if ($typeLabels->count() <= 2) {
                $labels[$groupNo] = $typeLabels->implode(' / ');
                continue;
            }

            $labels[$groupNo] = $typeLabels->take(2)->implode(' / ') . ' +' . ($typeLabels->count() - 2);
        }

        return $labels;
    }

    private function resolveTypeLabel(string $type): string
    {
        $normalized = strtoupper(trim($type));
        if ($normalized === '') {
            return '';
        }

        if (isset(FinanceAccount::TYPE_LABELS[$normalized])) {
            return (string) FinanceAccount::TYPE_LABELS[$normalized];
        }

        return str_replace('_', ' ', $normalized);
    }
}
