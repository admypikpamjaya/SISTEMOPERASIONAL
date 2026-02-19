@extends('layouts.app')

@section('section_name', isset($invoice) && $invoice ? 'Edit Faktur / Jurnal' : 'Buat Faktur / Jurnal')

@section('content')
@php
    $isEdit = isset($invoice) && $invoice;
    $isReadOnly = (bool) ($isReadOnly ?? false);
    $formAction = $isEdit ? route('finance.invoice.update', $invoice->id) : route('finance.invoice.store');
    $defaultDate = old('accounting_date', $isEdit ? optional($invoice->accounting_date)->toDateString() : now()->toDateString());
    $defaultEntryType = old('entry_type', $isEdit ? $invoice->entry_type : 'EXPENSE');
    $defaultJournalName = old('journal_name', $isEdit ? $invoice->journal_name : '');
    $defaultReference = old('reference', $isEdit ? $invoice->reference : '');
    $defaultAction = old('action', 'save_draft');

    $rowItems = old('items');
    if (!is_array($rowItems)) {
        $rowItems = collect($items ?? [])->map(function ($item) {
            return [
                'asset_category' => $item->asset_category ?? '',
                'account_code' => $item->account_code ?? '',
                'partner_name' => $item->partner_name ?? '',
                'label' => $item->label ?? '',
                'analytic_distribution' => $item->analytic_distribution ?? '',
                'debit' => isset($item->debit) ? (float) $item->debit : 0,
                'credit' => isset($item->credit) ? (float) $item->credit : 0,
            ];
        })->toArray();
    }

    if (empty($rowItems)) {
        $rowItems = [[
            'asset_category' => '',
            'account_code' => '',
            'partner_name' => '',
            'label' => '',
            'analytic_distribution' => '',
            'debit' => 0,
            'credit' => 0,
        ]];
    }
@endphp

@if($errors->any())
    <div class="alert alert-danger">
        <strong>Validasi gagal:</strong>
        <ul class="mb-0 mt-2 pl-3">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card card-outline card-primary">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">
            {{ $isEdit ? 'Ubah Faktur / Jurnal' : 'Draft Faktur / Jurnal Baru' }}
        </h3>
        <div class="btn-group">
            <a href="{{ route('finance.invoice.index') }}" class="btn btn-sm btn-default">
                <i class="fas fa-arrow-left mr-1"></i> Kembali
            </a>
            @if($isEdit && $invoice->status === 'POSTED')
                <form action="{{ route('finance.invoice.set-draft', $invoice->id) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-warning">
                        <i class="fas fa-undo mr-1"></i> Reset ke Rancangan
                    </button>
                </form>
            @endif
        </div>
    </div>

    <form method="POST" action="{{ $formAction }}" id="invoice-form">
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif
        <input type="hidden" name="action" id="form-action" value="{{ $defaultAction }}">

        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h4 class="mb-1">{{ $isEdit ? $invoice->invoice_no : 'Draft' }}</h4>
                    <div class="text-muted small">
                        {{ $isEdit ? 'Faktur: ' . $invoice->invoice_no : 'Nomor faktur akan dibuat otomatis saat disimpan.' }}
                    </div>
                </div>
                <div>
                    @php
                        $status = strtoupper((string) ($isEdit ? $invoice->status : 'DRAFT'));
                        $statusClass = $status === 'POSTED'
                            ? 'badge-success'
                            : ($status === 'CANCELLED' ? 'badge-secondary' : 'badge-warning');
                    @endphp
                    <span class="badge {{ $statusClass }} p-2">{{ $status }}</span>
                </div>
            </div>

            @if($isReadOnly)
                <div class="alert alert-info">
                    Faktur ini sudah terekam. Klik <strong>Reset ke Rancangan</strong> untuk mengedit isi jurnal.
                </div>
            @endif

            <div class="form-row">
                <div class="form-group col-md-3">
                    <label for="accounting_date">Tanggal Akuntansi</label>
                    <input
                        type="date"
                        name="accounting_date"
                        id="accounting_date"
                        class="form-control"
                        value="{{ $defaultDate }}"
                        {{ $isReadOnly ? 'disabled' : '' }}
                        required
                    >
                </div>
                <div class="form-group col-md-3">
                    <label for="entry_type">Jenis</label>
                    <select
                        name="entry_type"
                        id="entry_type"
                        class="form-control"
                        {{ $isReadOnly ? 'disabled' : '' }}
                        required
                    >
                        <option value="INCOME" {{ $defaultEntryType === 'INCOME' ? 'selected' : '' }}>Pemasukan</option>
                        <option value="EXPENSE" {{ $defaultEntryType === 'EXPENSE' ? 'selected' : '' }}>Pengeluaran</option>
                    </select>
                </div>
                <div class="form-group col-md-6">
                    <label for="journal_name">Jurnal</label>
                    <input
                        type="text"
                        name="journal_name"
                        id="journal_name"
                        class="form-control"
                        placeholder="Contoh: J.BSM.PMB-Keluar"
                        value="{{ $defaultJournalName }}"
                        {{ $isReadOnly ? 'disabled' : '' }}
                        required
                    >
                </div>
                <div class="form-group col-md-12">
                    <label for="reference">Referensi</label>
                    <input
                        type="text"
                        name="reference"
                        id="reference"
                        class="form-control"
                        placeholder="Contoh: Pembayaran SPP Februari 2026"
                        value="{{ $defaultReference }}"
                        {{ $isReadOnly ? 'disabled' : '' }}
                    >
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-sm mb-0" id="invoice-items-table">
                    <thead>
                        <tr>
                            <th style="width: 140px;">Asset Category</th>
                            <th style="width: 140px;">Akun</th>
                            <th style="width: 160px;">Rekanan</th>
                            <th style="width: 220px;">Label</th>
                            <th>Analisa Distribusi</th>
                            <th style="width: 160px;">Debit</th>
                            <th style="width: 160px;">Kredit</th>
                            <th style="width: 70px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="invoice-items-body">
                        @foreach($rowItems as $index => $item)
                            <tr>
                                <td>
                                    <input type="text" name="items[{{ $index }}][asset_category]" class="form-control form-control-sm" value="{{ $item['asset_category'] ?? '' }}" {{ $isReadOnly ? 'disabled' : '' }}>
                                </td>
                                <td>
                                    <input type="text" name="items[{{ $index }}][account_code]" class="form-control form-control-sm" value="{{ $item['account_code'] ?? '' }}" required {{ $isReadOnly ? 'disabled' : '' }}>
                                </td>
                                <td>
                                    <input type="text" name="items[{{ $index }}][partner_name]" class="form-control form-control-sm" value="{{ $item['partner_name'] ?? '' }}" {{ $isReadOnly ? 'disabled' : '' }}>
                                </td>
                                <td>
                                    <input type="text" name="items[{{ $index }}][label]" class="form-control form-control-sm" value="{{ $item['label'] ?? '' }}" required {{ $isReadOnly ? 'disabled' : '' }}>
                                </td>
                                <td>
                                    <input type="text" name="items[{{ $index }}][analytic_distribution]" class="form-control form-control-sm" value="{{ $item['analytic_distribution'] ?? '' }}" {{ $isReadOnly ? 'disabled' : '' }}>
                                </td>
                                <td>
                                    <input type="number" min="0" step="0.01" name="items[{{ $index }}][debit]" class="form-control form-control-sm text-right js-amount-debit" value="{{ $item['debit'] ?? 0 }}" {{ $isReadOnly ? 'disabled' : '' }}>
                                </td>
                                <td>
                                    <input type="number" min="0" step="0.01" name="items[{{ $index }}][credit]" class="form-control form-control-sm text-right js-amount-credit" value="{{ $item['credit'] ?? 0 }}" {{ $isReadOnly ? 'disabled' : '' }}>
                                </td>
                                <td class="text-center">
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-danger js-remove-row"
                                        data-locked="{{ $isReadOnly ? '1' : '0' }}"
                                        {{ $isReadOnly ? 'disabled' : '' }}
                                    >
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="5" class="text-right">Total</th>
                            <th class="text-right" id="total-debit-text">Rp 0,00</th>
                            <th class="text-right" id="total-credit-text">Rp 0,00</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="form-group mt-3">
                <label for="initial_note">Catatan Awal (opsional)</label>
                <textarea
                    name="initial_note"
                    id="initial_note"
                    rows="2"
                    class="form-control"
                    placeholder="Catatan untuk log faktur (bisa ditambah lagi di halaman detail)."
                    {{ $isReadOnly ? 'disabled' : '' }}
                >{{ old('initial_note') }}</textarea>
            </div>
        </div>

        <div class="card-footer d-flex justify-content-between">
            <button type="button" id="add-item-row" class="btn btn-default" {{ $isReadOnly ? 'disabled' : '' }}>
                <i class="fas fa-plus mr-1"></i> Tambahkan Baris
            </button>
            @if(!$isReadOnly)
                <div>
                    <button type="submit" class="btn btn-secondary mr-2 js-submit-action" data-action="save_draft">
                        <i class="fas fa-save mr-1"></i> Simpan Draft
                    </button>
                    <button type="submit" class="btn btn-primary js-submit-action" data-action="post">
                        <i class="fas fa-check mr-1"></i> Rekam
                    </button>
                </div>
            @endif
        </div>
    </form>
</div>
@endsection

@section('js')
<script>
    (function () {
        const tableBody = document.getElementById('invoice-items-body');
        const addButton = document.getElementById('add-item-row');
        const form = document.getElementById('invoice-form');
        const actionInput = document.getElementById('form-action');
        const totalDebitText = document.getElementById('total-debit-text');
        const totalCreditText = document.getElementById('total-credit-text');

        function parseNumber(value) {
            const number = Number(value);
            return Number.isFinite(number) ? number : 0;
        }

        function formatRupiah(value) {
            return 'Rp ' + Number(value).toLocaleString('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        function updateTotals() {
            let totalDebit = 0;
            let totalCredit = 0;

            Array.from(tableBody.querySelectorAll('tr')).forEach(function (row) {
                const debitInput = row.querySelector('.js-amount-debit');
                const creditInput = row.querySelector('.js-amount-credit');
                totalDebit += parseNumber(debitInput ? debitInput.value : 0);
                totalCredit += parseNumber(creditInput ? creditInput.value : 0);
            });

            totalDebitText.textContent = formatRupiah(totalDebit);
            totalCreditText.textContent = formatRupiah(totalCredit);
        }

        function renumberRows() {
            Array.from(tableBody.querySelectorAll('tr')).forEach(function (row, index) {
                row.querySelectorAll('input').forEach(function (input) {
                    const name = input.getAttribute('name');
                    if (!name) {
                        return;
                    }

                    input.setAttribute('name', name.replace(/items\[\d+\]/, 'items[' + index + ']'));
                });

                const removeButton = row.querySelector('.js-remove-row');
                if (removeButton) {
                    const isLocked = removeButton.getAttribute('data-locked') === '1';
                    removeButton.disabled = isLocked || tableBody.querySelectorAll('tr').length <= 1;
                }
            });
        }

        function addRow() {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td><input type="text" name="items[0][asset_category]" class="form-control form-control-sm"></td>
                <td><input type="text" name="items[0][account_code]" class="form-control form-control-sm" required></td>
                <td><input type="text" name="items[0][partner_name]" class="form-control form-control-sm"></td>
                <td><input type="text" name="items[0][label]" class="form-control form-control-sm" required></td>
                <td><input type="text" name="items[0][analytic_distribution]" class="form-control form-control-sm"></td>
                <td><input type="number" min="0" step="0.01" name="items[0][debit]" class="form-control form-control-sm text-right js-amount-debit" value="0"></td>
                <td><input type="number" min="0" step="0.01" name="items[0][credit]" class="form-control form-control-sm text-right js-amount-credit" value="0"></td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger js-remove-row" data-locked="0">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;

            tableBody.appendChild(row);
            renumberRows();
            updateTotals();
        }

        if (addButton) {
            addButton.addEventListener('click', addRow);
        }

        if (tableBody) {
            tableBody.addEventListener('click', function (event) {
                const button = event.target.closest('.js-remove-row');
                if (!button) {
                    return;
                }

                const row = button.closest('tr');
                if (!row) {
                    return;
                }

                if (tableBody.querySelectorAll('tr').length <= 1) {
                    return;
                }

                row.remove();
                renumberRows();
                updateTotals();
            });

            tableBody.addEventListener('input', function (event) {
                if (event.target.matches('.js-amount-debit, .js-amount-credit')) {
                    updateTotals();
                }
            });
        }

        Array.from(document.querySelectorAll('.js-submit-action')).forEach(function (button) {
            button.addEventListener('click', function () {
                actionInput.value = button.dataset.action || 'save_draft';
            });
        });

        if (form) {
            form.addEventListener('submit', function () {
                if (!actionInput.value) {
                    actionInput.value = 'save_draft';
                }
            });
        }

        renumberRows();
        updateTotals();
    })();
</script>
@endsection
