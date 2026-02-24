<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\FinanceAccountStoreRequest;
use App\Http\Requests\Finance\FinanceAccountUpdateRequest;
use App\Models\FinanceAccount;
use App\Models\FinanceAccountLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Throwable;

class FinanceAccountController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'group' => 'nullable|integer|in:1,2,3,4,5,8,9',
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
            'groupOrder' => FinanceAccount::classOrder(),
            'groupLabels' => FinanceAccount::CLASS_LABELS,
            'typeLabels' => FinanceAccount::TYPE_LABELS,
            'typeClassMap' => FinanceAccount::TYPE_CLASS_MAP,
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
            $classNo = FinanceAccount::classForType($type);
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
            $classNo = FinanceAccount::classForType($type);
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
}
