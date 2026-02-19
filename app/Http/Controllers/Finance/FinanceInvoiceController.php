<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\FinanceInvoiceNoteStoreRequest;
use App\Http\Requests\Finance\FinanceInvoiceStoreRequest;
use App\Http\Requests\Finance\FinanceInvoiceUpdateRequest;
use App\Models\FinanceInvoice;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class FinanceInvoiceController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => 'nullable|string|max:255',
            'status' => 'nullable|string|in:ALL,DRAFT,POSTED,CANCELLED',
            'entry_type' => 'nullable|string|in:ALL,INCOME,EXPENSE',
            'month' => 'nullable|integer|between:1,12',
            'year' => 'nullable|integer|digits:4|between:1900,2100',
            'per_page' => 'nullable|integer|min:5|max:100',
        ]);

        $query = FinanceInvoice::query()
            ->with(['creator:id,name'])
            ->orderByDesc('accounting_date')
            ->orderByDesc('created_at');

        $search = trim((string) ($filters['q'] ?? ''));
        if ($search !== '') {
            $query->where(function ($innerQuery) use ($search) {
                $innerQuery->where('invoice_no', 'like', '%' . $search . '%')
                    ->orWhere('journal_name', 'like', '%' . $search . '%')
                    ->orWhere('reference', 'like', '%' . $search . '%');
            });
        }

        $status = strtoupper((string) ($filters['status'] ?? 'ALL'));
        if ($status !== 'ALL') {
            $query->where('status', $status);
        }

        $entryType = strtoupper((string) ($filters['entry_type'] ?? 'ALL'));
        if ($entryType !== 'ALL') {
            $query->where('entry_type', $entryType);
        }

        $year = isset($filters['year']) ? (int) $filters['year'] : null;
        if ($year !== null) {
            $query->whereYear('accounting_date', $year);
        }

        $month = isset($filters['month']) ? (int) $filters['month'] : null;
        if ($month !== null) {
            $query->whereMonth('accounting_date', $month);
        }

        $perPage = (int) ($filters['per_page'] ?? 15);
        $invoices = $query->paginate($perPage)->withQueryString();

        return view('finance.invoices.index', [
            'invoices' => $invoices,
            'filters' => [
                'q' => $search,
                'status' => $status,
                'entry_type' => $entryType,
                'month' => $month,
                'year' => $year,
                'per_page' => $perPage,
            ],
        ]);
    }

    public function create()
    {
        return view('finance.invoices.form', [
            'invoice' => null,
            'items' => [],
            'isReadOnly' => false,
        ]);
    }

    public function store(FinanceInvoiceStoreRequest $request)
    {
        try {
            $validated = $request->validated();
            $actorId = auth()->id() ? (string) auth()->id() : null;

            $invoice = DB::transaction(function () use ($validated, $actorId) {
                [$rows, $totalDebit, $totalCredit] = $this->buildItemRows((array) $validated['items']);
                $action = strtolower((string) ($validated['action'] ?? 'save_draft'));
                $isPost = $action === 'post';

                $invoice = FinanceInvoice::query()->create([
                    'invoice_no' => $this->generateInvoiceNo(
                        (string) $validated['accounting_date'],
                        (string) $validated['entry_type']
                    ),
                    'accounting_date' => $validated['accounting_date'],
                    'entry_type' => $validated['entry_type'],
                    'journal_name' => $validated['journal_name'],
                    'reference' => $validated['reference'] ?? null,
                    'status' => $isPost ? 'POSTED' : 'DRAFT',
                    'total_debit' => $totalDebit,
                    'total_credit' => $totalCredit,
                    'created_by' => $actorId,
                    'updated_by' => $actorId,
                    'posted_by' => $isPost ? $actorId : null,
                    'posted_at' => $isPost ? now() : null,
                ]);

                if (!empty($rows)) {
                    $invoice->items()->createMany($rows);
                }

                if (!empty($validated['initial_note'])) {
                    $invoice->notes()->create([
                        'user_id' => $actorId,
                        'note' => (string) $validated['initial_note'],
                    ]);
                }

                if ($isPost) {
                    $invoice->notes()->create([
                        'user_id' => $actorId,
                        'note' => 'Faktur dibuat dan langsung direkam.',
                    ]);
                }

                return $invoice;
            });

            return redirect()
                ->route('finance.invoice.show', $invoice->id)
                ->with('success', 'Faktur/jurnal berhasil dibuat.');
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal membuat faktur/jurnal.');
        }
    }

    public function show(FinanceInvoice $invoice)
    {
        $invoice->load([
            'items',
            'notes.user:id,name,role',
            'creator:id,name',
            'poster:id,name',
            'updater:id,name',
        ]);

        return view('finance.invoices.show', [
            'invoice' => $invoice,
        ]);
    }

    public function edit(FinanceInvoice $invoice)
    {
        $invoice->load('items');
        $isReadOnly = $invoice->status === 'POSTED';

        return view('finance.invoices.form', [
            'invoice' => $invoice,
            'items' => $invoice->items,
            'isReadOnly' => $isReadOnly,
        ]);
    }

    public function update(FinanceInvoiceUpdateRequest $request, FinanceInvoice $invoice)
    {
        if ($invoice->status === 'POSTED') {
            return redirect()
                ->route('finance.invoice.show', $invoice->id)
                ->with('error', 'Faktur yang sudah terekam harus dikembalikan ke draft sebelum diedit.');
        }

        try {
            $validated = $request->validated();
            $actorId = auth()->id() ? (string) auth()->id() : null;

            DB::transaction(function () use ($validated, $invoice, $actorId) {
                [$rows, $totalDebit, $totalCredit] = $this->buildItemRows((array) $validated['items']);
                $action = strtolower((string) ($validated['action'] ?? 'save_draft'));
                $isPost = $action === 'post';

                $invoice->update([
                    'accounting_date' => $validated['accounting_date'],
                    'entry_type' => $validated['entry_type'],
                    'journal_name' => $validated['journal_name'],
                    'reference' => $validated['reference'] ?? null,
                    'status' => $isPost ? 'POSTED' : 'DRAFT',
                    'total_debit' => $totalDebit,
                    'total_credit' => $totalCredit,
                    'updated_by' => $actorId,
                    'posted_by' => $isPost ? $actorId : null,
                    'posted_at' => $isPost ? now() : null,
                ]);

                $invoice->items()->delete();
                if (!empty($rows)) {
                    $invoice->items()->createMany($rows);
                }

                if (!empty($validated['initial_note'])) {
                    $invoice->notes()->create([
                        'user_id' => $actorId,
                        'note' => (string) $validated['initial_note'],
                    ]);
                }

                if ($isPost) {
                    $invoice->notes()->create([
                        'user_id' => $actorId,
                        'note' => 'Faktur diperbarui dan direkam.',
                    ]);
                }
            });

            return redirect()
                ->route('finance.invoice.show', $invoice->id)
                ->with('success', 'Faktur/jurnal berhasil diperbarui.');
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui faktur/jurnal.');
        }
    }

    public function destroy(FinanceInvoice $invoice)
    {
        if ($invoice->status === 'POSTED') {
            return redirect()
                ->route('finance.invoice.show', $invoice->id)
                ->with('error', 'Faktur yang sudah terekam tidak bisa dihapus.');
        }

        $invoice->delete();

        return redirect()
            ->route('finance.invoice.index')
            ->with('success', 'Faktur/jurnal berhasil dihapus.');
    }

    public function post(FinanceInvoice $invoice)
    {
        $invoice->load('items');
        if ($invoice->items->count() === 0) {
            return redirect()
                ->route('finance.invoice.show', $invoice->id)
                ->with('error', 'Item jurnal kosong. Tidak dapat merekam.');
        }

        $totalDebit = round((float) $invoice->items->sum('debit'), 2);
        $totalCredit = round((float) $invoice->items->sum('credit'), 2);

        if ($totalDebit <= 0 || $totalCredit <= 0 || $totalDebit !== $totalCredit) {
            return redirect()
                ->route('finance.invoice.show', $invoice->id)
                ->with('error', 'Total debit dan kredit harus seimbang sebelum direkam.');
        }

        $invoice->update([
            'status' => 'POSTED',
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'posted_by' => auth()->id() ? (string) auth()->id() : null,
            'posted_at' => now(),
            'updated_by' => auth()->id() ? (string) auth()->id() : null,
        ]);
        $invoice->notes()->create([
            'user_id' => auth()->id() ? (string) auth()->id() : null,
            'note' => 'Faktur direkam (status POSTED).',
        ]);

        return redirect()
            ->route('finance.invoice.show', $invoice->id)
            ->with('success', 'Faktur/jurnal berhasil direkam.');
    }

    public function setDraft(FinanceInvoice $invoice)
    {
        $invoice->update([
            'status' => 'DRAFT',
            'posted_by' => null,
            'posted_at' => null,
            'updated_by' => auth()->id() ? (string) auth()->id() : null,
        ]);
        $invoice->notes()->create([
            'user_id' => auth()->id() ? (string) auth()->id() : null,
            'note' => 'Status faktur dikembalikan ke DRAFT.',
        ]);

        return redirect()
            ->route('finance.invoice.show', $invoice->id)
            ->with('success', 'Status faktur dikembalikan ke draft.');
    }

    public function storeNote(FinanceInvoiceNoteStoreRequest $request, FinanceInvoice $invoice)
    {
        $invoice->notes()->create([
            'user_id' => auth()->id() ? (string) auth()->id() : null,
            'note' => (string) $request->validated()['note'],
        ]);

        return redirect()
            ->route('finance.invoice.show', $invoice->id)
            ->with('success', 'Catatan berhasil ditambahkan.');
    }

    /**
     * @param array<int, array<string, mixed>> $items
     * @return array{0:array<int, array<string, mixed>>, 1:float, 2:float}
     */
    private function buildItemRows(array $items): array
    {
        $rows = [];
        $totalDebit = 0.0;
        $totalCredit = 0.0;

        foreach ($items as $index => $item) {
            $debit = round((float) ($item['debit'] ?? 0), 2);
            $credit = round((float) ($item['credit'] ?? 0), 2);
            $totalDebit += $debit;
            $totalCredit += $credit;

            $rows[] = [
                'asset_category' => $item['asset_category'] ?? null,
                'account_code' => (string) ($item['account_code'] ?? ''),
                'partner_name' => $item['partner_name'] ?? null,
                'label' => (string) ($item['label'] ?? ''),
                'analytic_distribution' => $item['analytic_distribution'] ?? null,
                'debit' => $debit,
                'credit' => $credit,
                'sort_order' => $index + 1,
            ];
        }

        return [$rows, round($totalDebit, 2), round($totalCredit, 2)];
    }

    private function generateInvoiceNo(string $accountingDate, string $entryType): string
    {
        $date = Carbon::parse($accountingDate);
        $prefix = strtoupper($entryType) === 'INCOME' ? 'JMAS' : 'JKEL';
        $base = sprintf('%s/%s/%s/', $prefix, $date->format('Y'), $date->format('m'));

        $latestNo = FinanceInvoice::query()
            ->where('invoice_no', 'like', $base . '%')
            ->lockForUpdate()
            ->orderByDesc('invoice_no')
            ->value('invoice_no');

        $lastSequence = 0;
        if (is_string($latestNo) && preg_match('/(\d{4})$/', $latestNo, $matches)) {
            $lastSequence = (int) $matches[1];
        }

        return $base . str_pad((string) ($lastSequence + 1), 4, '0', STR_PAD_LEFT);
    }
}
