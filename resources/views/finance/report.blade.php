@extends('layouts.app')

@section('section_name', 'Finance Report')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-outline card-success">
            <div class="card-header">
                <h3 class="card-title mb-0">Input Laporan Laba Rugi</h3>
            </div>
            <form method="POST" action="{{ route('finance.report.store') }}" id="profit-loss-form">
                @csrf
                <div class="card-body">
                    @php
                        $entryRows = old('entries', [
                            [
                                'type' => 'INCOME',
                                'line_code' => '',
                                'line_label' => '',
                                'description' => '',
                                'amount' => '',
                                'is_depreciation' => false,
                            ],
                        ]);
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

                    <div class="form-row">
                        <div class="form-group col-md-2">
                            <label for="report_type_create">Tipe</label>
                            <select name="report_type" id="report_type_create" class="form-control" required>
                                <option value="MONTHLY" {{ old('report_type', 'MONTHLY') === 'MONTHLY' ? 'selected' : '' }}>MONTHLY</option>
                                <option value="YEARLY" {{ old('report_type') === 'YEARLY' ? 'selected' : '' }}>YEARLY</option>
                            </select>
                        </div>
                        <div class="form-group col-md-2">
                            <label for="month_create">Bulan</label>
                            <select name="month" id="month_create" class="form-control">
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ (int) old('month', now()->month) === $m ? 'selected' : '' }}>{{ $m }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="form-group col-md-2">
                            <label for="year_create">Tahun</label>
                            <input type="number" name="year" id="year_create" class="form-control" min="1900" max="2100" value="{{ old('year', now()->year) }}" required>
                        </div>
                        <div class="form-group col-md-3">
                            <label for="opening_balance_create">Saldo Awal</label>
                            <input
                                type="number"
                                name="opening_balance"
                                id="opening_balance_create"
                                class="form-control"
                                step="0.01"
                                value="{{ old('opening_balance', 0) }}"
                                required
                            >
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm mb-0" id="profit-loss-lines-table">
                            <thead>
                                <tr>
                                    <th style="width: 130px;">Jenis</th>
                                    <th style="width: 140px;">Kode Akun</th>
                                    <th style="width: 220px;">Nama Akun</th>
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

<div class="row">
    <div class="col-12">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title mb-0">Filter Laporan Finance</h3>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('finance.report.index') }}" class="form-row">
                    <div class="form-group col-md-2">
                        <label for="month">Bulan</label>
                        <select name="month" id="month" class="form-control">
                            <option value="">Semua</option>
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ (int) ($filters['month'] ?? 0) === $m ? 'selected' : '' }}>
                                    {{ $m }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="year">Tahun</label>
                        <input
                            type="number"
                            name="year"
                            id="year"
                            class="form-control"
                            min="1900"
                            max="2100"
                            value="{{ $filters['year'] }}"
                            required
                        >
                    </div>
                    <div class="form-group col-md-3">
                        <label for="report_type">Tipe</label>
                        <select name="report_type" id="report_type" class="form-control">
                            <option value="">Semua</option>
                            <option value="MONTHLY" {{ ($filters['report_type'] ?? null) === 'MONTHLY' ? 'selected' : '' }}>MONTHLY</option>
                            <option value="YEARLY" {{ ($filters['report_type'] ?? null) === 'YEARLY' ? 'selected' : '' }}>YEARLY</option>
                        </select>
                    </div>
                    <div class="form-group col-md-2">
                        <label for="per_page">Per Page</label>
                        <select name="per_page" id="per_page" class="form-control">
                            @foreach([10, 20, 50, 100] as $size)
                                <option value="{{ $size }}" {{ (int) request('per_page', 20) === $size ? 'selected' : '' }}>
                                    {{ $size }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary mr-2">
                            <i class="fas fa-search mr-1"></i> Cari
                        </button>
                        <a href="{{ route('finance.report.index', ['year' => now()->year]) }}" class="btn btn-default">Reset</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title mb-0">Daftar Snapshot Laporan</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Aksi</th>
                            <th>Tipe</th>
                            <th>Versi</th>
                            <th>Saldo Akhir</th>
                            <th>Generated At</th>
                            <th>Generated By</th>
                            <th>Read Only</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reports as $report)
                            <tr>
                                <td>
                                    <a href="{{ route('finance.report.show', $report->id) }}" class="btn btn-sm btn-outline-primary">
                                        Preview
                                    </a>
                                </td>
                                <td>{{ $report->report_type }}</td>
                                <td>{{ $report->version_no }}</td>
                                <td>
                                    {{ number_format((float) data_get($report->summary, 'ending_balance', data_get($report->summary, 'net_result', 0)), 2, ',', '.') }}
                                </td>
                                <td>{{ optional($report->generated_at)->format('Y-m-d H:i:s') ?? '-' }}</td>
                                <td>{{ $report->user?->name ?? '-' }}</td>
                                <td>{{ $report->is_read_only ? 'Yes' : 'No' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">Tidak ada snapshot laporan untuk filter ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer clearfix">
                {{ $reports->appends(request()->query())->links() }}
            </div>
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
        const monthSelect = document.getElementById('month_create');

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
        }

        function syncMonthField() {
            if (reportTypeSelect.value === 'YEARLY') {
                monthSelect.value = '';
                monthSelect.setAttribute('disabled', 'disabled');
            } else {
                monthSelect.removeAttribute('disabled');
                if (!monthSelect.value) {
                    monthSelect.value = '{{ now()->month }}';
                }
            }
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
        });

        tableBody.addEventListener('change', function (event) {
            if (event.target.matches('select[name*="[type]"]')) {
                const row = event.target.closest('tr');
                if (row) {
                    syncDepreciationCheckbox(row);
                }
            }
        });

        reportTypeSelect.addEventListener('change', syncMonthField);

        renumberRows();
        syncMonthField();
    })();
</script>
@endsection
