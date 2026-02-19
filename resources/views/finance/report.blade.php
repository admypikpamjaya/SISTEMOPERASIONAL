@extends('layouts.app')

@section('section_name', 'Finance Report')

@section('content')
@php
    $defaultPeriodType = old('report_type', $defaults['period_type'] ?? 'MONTHLY');
    $defaultReportDate = old('report_date', $defaults['report_date'] ?? now()->toDateString());
    $defaultMonth = (int) old('month', $defaults['month'] ?? now()->month);
    $defaultYear = (int) old('year', $defaults['year'] ?? now()->year);
    $defaultOpeningBalance = old('opening_balance', $suggestedOpeningBalance ?? 0);

    $entryRows = old('entries', [
        [
            'type' => 'INCOME',
            'line_code' => '',
            'line_label' => '',
            'invoice_number' => '',
            'description' => '',
            'amount' => '',
            'is_depreciation' => false,
        ],
    ]);
@endphp

<div class="row">
    <div class="col-12">
        <div class="card card-outline card-success">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">Input Laporan Laba Rugi</h3>
                <a
                    href="{{ route('finance.report.snapshots', ['period_type' => 'MONTHLY', 'month' => now()->month, 'year' => now()->year]) }}"
                    class="btn btn-sm btn-outline-primary"
                >
                    <i class="fas fa-list mr-1"></i> Buka Snapshot Laporan
                </a>
            </div>
            <form method="POST" action="{{ route('finance.report.store') }}" id="profit-loss-form">
                @csrf
                <div class="card-body">
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

                    <div class="form-row">
                        <div class="form-group col-md-2">
                            <label for="report_type_create">Periode</label>
                            <select name="report_type" id="report_type_create" class="form-control" required>
                                <option value="DAILY" {{ $defaultPeriodType === 'DAILY' ? 'selected' : '' }}>Harian</option>
                                <option value="MONTHLY" {{ $defaultPeriodType === 'MONTHLY' ? 'selected' : '' }}>Bulanan</option>
                                <option value="YEARLY" {{ $defaultPeriodType === 'YEARLY' ? 'selected' : '' }}>Tahunan</option>
                            </select>
                        </div>

                        <div class="form-group col-md-2" id="report_date_group">
                            <label for="report_date_create">Tanggal</label>
                            <input
                                type="date"
                                name="report_date"
                                id="report_date_create"
                                class="form-control"
                                value="{{ $defaultReportDate }}"
                            >
                        </div>

                        <div class="form-group col-md-2" id="month_group">
                            <label for="month_create">Bulan</label>
                            <select name="month" id="month_create" class="form-control">
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ $defaultMonth === $m ? 'selected' : '' }}>{{ $m }}</option>
                                @endfor
                            </select>
                        </div>

                        <div class="form-group col-md-2" id="year_group">
                            <label for="year_create">Tahun</label>
                            <input
                                type="number"
                                name="year"
                                id="year_create"
                                class="form-control"
                                min="1900"
                                max="2100"
                                value="{{ $defaultYear }}"
                            >
                        </div>

                        <div class="form-group col-md-4">
                            <label for="opening_balance_create">Saldo Awal</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">Rp</span>
                                </div>
                                <input
                                    type="number"
                                    name="opening_balance"
                                    id="opening_balance_create"
                                    class="form-control"
                                    step="0.01"
                                    min="0"
                                    value="{{ $defaultOpeningBalance }}"
                                    required
                                >
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-light border mb-3">
                        <strong>Estimasi Saldo Akhir:</strong>
                        <span id="estimated-ending-balance">Rp 0,00</span>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm mb-0" id="profit-loss-lines-table">
                            <thead>
                                <tr>
                                    <th style="width: 130px;">Jenis</th>
                                    <th style="width: 140px;">Kode Akun</th>
                                    <th style="width: 220px;">Nama Akun</th>
                                    <th style="width: 200px;">Nomor Faktur</th>
                                    <th>Keterangan</th>
                                    <th style="width: 180px;">Nominal</th>
                                    <th style="width: 130px;">Penyusutan</th>
                                    <th style="width: 80px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="profit-loss-lines-body">
                                @foreach($entryRows as $index => $entry)
                                    <tr>
                                        <td>
                                            <select name="entries[{{ $index }}][type]" class="form-control form-control-sm" required>
                                                <option value="INCOME" {{ ($entry['type'] ?? 'INCOME') === 'INCOME' ? 'selected' : '' }}>INCOME</option>
                                                <option value="EXPENSE" {{ ($entry['type'] ?? null) === 'EXPENSE' ? 'selected' : '' }}>EXPENSE</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input
                                                type="text"
                                                name="entries[{{ $index }}][line_code]"
                                                class="form-control form-control-sm"
                                                value="{{ $entry['line_code'] ?? '' }}"
                                                required
                                            >
                                        </td>
                                        <td>
                                            <input
                                                type="text"
                                                name="entries[{{ $index }}][line_label]"
                                                class="form-control form-control-sm"
                                                value="{{ $entry['line_label'] ?? '' }}"
                                                required
                                            >
                                        </td>
                                        <td>
                                            <input
                                                type="text"
                                                name="entries[{{ $index }}][invoice_number]"
                                                class="form-control form-control-sm"
                                                value="{{ $entry['invoice_number'] ?? '' }}"
                                                placeholder="Nomor faktur (opsional)"
                                                maxlength="100"
                                            >
                                        </td>
                                        <td>
                                            <input
                                                type="text"
                                                name="entries[{{ $index }}][description]"
                                                class="form-control form-control-sm"
                                                value="{{ $entry['description'] ?? '' }}"
                                                placeholder="Keterangan detail pemasukan/pengeluaran"
                                            >
                                        </td>
                                        <td>
                                            <input
                                                type="number"
                                                name="entries[{{ $index }}][amount]"
                                                class="form-control form-control-sm"
                                                min="0"
                                                step="0.01"
                                                value="{{ $entry['amount'] ?? '' }}"
                                                required
                                            >
                                        </td>
                                        <td class="text-center">
                                            <input
                                                type="checkbox"
                                                name="entries[{{ $index }}][is_depreciation]"
                                                value="1"
                                                {{ !empty($entry['is_depreciation']) ? 'checked' : '' }}
                                            >
                                        </td>
                                        <td class="text-center">
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-danger js-remove-row"
                                                {{ count($entryRows) === 1 ? 'disabled' : '' }}
                                            >
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="button" class="btn btn-default" id="add-profit-loss-line">
                        <i class="fas fa-plus mr-1"></i> Tambah Baris
                    </button>
                    <button type="submit" class="btn btn-success float-right">
                        <i class="fas fa-save mr-1"></i> Simpan Snapshot Laba Rugi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    (function () {
        const tableBody = document.getElementById('profit-loss-lines-body');
        const addButton = document.getElementById('add-profit-loss-line');
        const reportTypeSelect = document.getElementById('report_type_create');
        const reportDateGroup = document.getElementById('report_date_group');
        const reportDateInput = document.getElementById('report_date_create');
        const monthGroup = document.getElementById('month_group');
        const monthSelect = document.getElementById('month_create');
        const yearGroup = document.getElementById('year_group');
        const yearInput = document.getElementById('year_create');
        const openingBalanceInput = document.getElementById('opening_balance_create');
        const estimatedEndingBalance = document.getElementById('estimated-ending-balance');

        function parseAmount(value) {
            const number = Number(value);
            return Number.isFinite(number) ? number : 0;
        }

        function formatRupiah(value) {
            return 'Rp ' + Number(value).toLocaleString('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        function updateEstimatedBalance() {
            let balance = parseAmount(openingBalanceInput.value);

            Array.from(tableBody.querySelectorAll('tr')).forEach(function (row) {
                const typeSelect = row.querySelector('select[name*="[type]"]');
                const amountInput = row.querySelector('input[name*="[amount]"]');
                const depreciationInput = row.querySelector('input[name*="[is_depreciation]"]');

                if (!typeSelect || !amountInput) {
                    return;
                }

                const amount = parseAmount(amountInput.value);
                if (typeSelect.value === 'INCOME') {
                    balance += amount;
                    return;
                }

                const isDepreciation = depreciationInput ? depreciationInput.checked : false;
                if (!isDepreciation) {
                    balance -= amount;
                }
            });

            estimatedEndingBalance.textContent = formatRupiah(balance);
        }

        function syncDepreciationCheckbox(row) {
            const typeSelect = row.querySelector('select[name*="[type]"]');
            const checkbox = row.querySelector('input[type="checkbox"][name*="[is_depreciation]"]');
            if (!typeSelect || !checkbox) {
                return;
            }

            if (typeSelect.value === 'INCOME') {
                checkbox.checked = false;
                checkbox.disabled = true;
            } else {
                checkbox.disabled = false;
            }
        }

        function renumberRows() {
            Array.from(tableBody.querySelectorAll('tr')).forEach(function (row, index) {
                row.querySelectorAll('input, select').forEach(function (input) {
                    const name = input.getAttribute('name');
                    if (!name) {
                        return;
                    }

                    input.setAttribute('name', name.replace(/entries\[\d+\]/, 'entries[' + index + ']'));
                });

                const removeButton = row.querySelector('.js-remove-row');
                if (removeButton) {
                    removeButton.disabled = tableBody.querySelectorAll('tr').length === 1;
                }

                syncDepreciationCheckbox(row);
            });
        }

        function createRow() {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>
                    <select name="entries[0][type]" class="form-control form-control-sm" required>
                        <option value="INCOME">INCOME</option>
                        <option value="EXPENSE">EXPENSE</option>
                    </select>
                </td>
                <td>
                    <input type="text" name="entries[0][line_code]" class="form-control form-control-sm" required>
                </td>
                <td>
                    <input type="text" name="entries[0][line_label]" class="form-control form-control-sm" required>
                </td>
                <td>
                    <input
                        type="text"
                        name="entries[0][invoice_number]"
                        class="form-control form-control-sm"
                        placeholder="Nomor faktur (opsional)"
                        maxlength="100"
                    >
                </td>
                <td>
                    <input type="text" name="entries[0][description]" class="form-control form-control-sm" placeholder="Keterangan detail pemasukan/pengeluaran">
                </td>
                <td>
                    <input type="number" name="entries[0][amount]" class="form-control form-control-sm" min="0" step="0.01" required>
                </td>
                <td class="text-center">
                    <input type="checkbox" name="entries[0][is_depreciation]" value="1">
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger js-remove-row">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;

            tableBody.appendChild(row);
            renumberRows();
            updateEstimatedBalance();
        }

        function syncPeriodFields() {
            const reportType = reportTypeSelect.value;

            const isDaily = reportType === 'DAILY';
            const isMonthly = reportType === 'MONTHLY';
            const isYearly = reportType === 'YEARLY';

            reportDateGroup.style.display = isDaily ? '' : 'none';
            monthGroup.style.display = isMonthly ? '' : 'none';
            yearGroup.style.display = (isMonthly || isYearly) ? '' : 'none';

            reportDateInput.disabled = !isDaily;
            reportDateInput.required = isDaily;

            monthSelect.disabled = !isMonthly;
            monthSelect.required = isMonthly;

            yearInput.disabled = !(isMonthly || isYearly);
            yearInput.required = (isMonthly || isYearly);
        }

        addButton.addEventListener('click', createRow);

        tableBody.addEventListener('click', function (event) {
            const button = event.target.closest('.js-remove-row');
            if (!button) {
                return;
            }

            const row = button.closest('tr');
            if (!row) {
                return;
            }

            if (tableBody.querySelectorAll('tr').length === 1) {
                return;
            }

            row.remove();
            renumberRows();
            updateEstimatedBalance();
        });

        tableBody.addEventListener('change', function (event) {
            if (event.target.matches('select[name*="[type]"]')) {
                const row = event.target.closest('tr');
                if (row) {
                    syncDepreciationCheckbox(row);
                }
            }

            if (event.target.matches('select[name*="[type]"], input[name*="[amount]"], input[name*="[is_depreciation]"]')) {
                updateEstimatedBalance();
            }
        });

        tableBody.addEventListener('input', function (event) {
            if (event.target.matches('input[name*="[amount]"]')) {
                updateEstimatedBalance();
            }
        });

        reportTypeSelect.addEventListener('change', syncPeriodFields);
        openingBalanceInput.addEventListener('input', updateEstimatedBalance);

        renumberRows();
        syncPeriodFields();
        updateEstimatedBalance();
    })();
</script>
@endsection
