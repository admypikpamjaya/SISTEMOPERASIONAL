@extends('layouts.app')

@section('section_name', 'Asset Depreciation')

@section('content')
@php
    $nowWib = now(config('app.timezone'));
@endphp
<div class="row">
    <div class="col-lg-6 col-md-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title mb-0">Kalkulasi Penyusutan Garis Lurus</h3>
            </div>
            <form id="depreciation-form" action="{{ route('finance.depreciation.calc') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <label for="asset_id">Asset ID</label>
                        <select id="asset_id" name="asset_id" class="form-control" required>
                            <option value="">Pilih asset dari database</option>
                            @foreach(($assets ?? collect()) as $asset)
                                <option value="{{ $asset->id }}">
                                    #{{ $asset->id }} - {{ $asset->account_code }} ({{ $asset->category }})
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">
                            Total asset tersedia: {{ ($assets ?? collect())->count() }}
                        </small>
                    </div>
                    <div class="form-group">
                        <label for="acquisition_cost">Nilai Perolehan</label>
                        <input type="number" id="acquisition_cost" name="acquisition_cost" class="form-control" min="0" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="useful_life_months">Umur Manfaat (bulan)</label>
                        <input type="number" id="useful_life_months" name="useful_life_months" class="form-control" min="1" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="month">Bulan</label>
                            <select id="month" name="month" class="form-control" required>
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ $nowWib->month === $m ? 'selected' : '' }}>{{ $m }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="year">Tahun</label>
                            <input type="number" id="year" name="year" class="form-control" min="1900" max="2100" value="{{ $nowWib->year }}" required>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-calculator mr-1"></i> Hitung
                    </button>
                </div>
            </form>
        </div>
    </div>
    <div class="col-lg-6 col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title mb-0">Hasil Perhitungan</h3>
            </div>
            <div class="card-body">
                <div id="depreciation-alert" class="alert d-none" role="alert"></div>
                <dl class="row mb-0">
                    <dt class="col-sm-5">Asset ID</dt>
                    <dd class="col-sm-7" id="result-asset-id">-</dd>

                    <dt class="col-sm-5">Nilai Perolehan</dt>
                    <dd class="col-sm-7" id="result-acquisition-cost">-</dd>

                    <dt class="col-sm-5">Umur Bulan</dt>
                    <dd class="col-sm-7" id="result-useful-life">-</dd>

                    <dt class="col-sm-5">Penyusutan / Bulan</dt>
                    <dd class="col-sm-7" id="result-depreciation-per-month">-</dd>

                    <dt class="col-sm-5">Periode</dt>
                    <dd class="col-sm-7" id="result-period">-</dd>

                    <dt class="col-sm-5">Waktu Hitung (WIB)</dt>
                    <dd class="col-sm-7" id="result-calculated-at">-</dd>
                </dl>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card mt-3">
            <div class="card-header">
                <h3 class="card-title mb-0">Log Kalkulasi Penyusutan</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Waktu Hitung (WIB)</th>
                            <th>Asset</th>
                            <th>Periode</th>
                            <th>Nilai Perolehan</th>
                            <th>Umur Manfaat (Bulan)</th>
                            <th>Penyusutan / Bulan</th>
                            <th>Dihitung Oleh</th>
                        </tr>
                    </thead>
                    <tbody id="depreciation-log-body">
                        @forelse(($logs ?? collect()) as $index => $log)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $log->calculated_at?->timezone(config('app.timezone'))->format('d/m/Y H:i:s') ?? '-' }}</td>
                                <td>
                                    <div><strong>{{ $log->asset?->account_code ?? '-' }}</strong></div>
                                    <small class="text-muted">ID: {{ $log->asset_id }}</small>
                                </td>
                                <td>{{ sprintf('%02d/%04d', (int) $log->period_month, (int) $log->period_year) }}</td>
                                <td>Rp {{ number_format((float) $log->acquisition_cost, 2, ',', '.') }}</td>
                                <td>{{ (int) $log->useful_life_months }}</td>
                                <td>Rp {{ number_format((float) $log->depreciation_per_month, 2, ',', '.') }}</td>
                                <td>{{ $log->calculator?->name ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr id="depreciation-log-empty-row">
                                <td colspan="8" class="text-center text-muted py-4">Belum ada log kalkulasi penyusutan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    (function () {
        const form = document.getElementById('depreciation-form');
        const alertBox = document.getElementById('depreciation-alert');
        const logBody = document.getElementById('depreciation-log-body');

        function setAlert(type, message) {
            alertBox.className = 'alert alert-' + type;
            alertBox.textContent = message;
            alertBox.classList.remove('d-none');
        }

        function formatNumber(value) {
            const number = Number(value);
            if (Number.isNaN(number)) {
                return '-';
            }

            return number.toLocaleString('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function renumberLogRows() {
            if (!logBody) {
                return;
            }

            const rows = Array.from(logBody.querySelectorAll('tr')).filter((row) => row.id !== 'depreciation-log-empty-row');
            rows.forEach((row, index) => {
                const cell = row.querySelector('td');
                if (cell) {
                    cell.textContent = String(index + 1);
                }
            });
        }

        function prependLogRow(log) {
            if (!logBody || !log) {
                return;
            }

            const emptyRow = document.getElementById('depreciation-log-empty-row');
            if (emptyRow) {
                emptyRow.remove();
            }

            const row = document.createElement('tr');
            row.innerHTML = `
                <td>1</td>
                <td>${escapeHtml(log.calculated_at_label || '-')}</td>
                <td>
                    <div><strong>${escapeHtml(log.asset_account_code || '-')}</strong></div>
                    <small class="text-muted">ID: ${escapeHtml(log.asset_id || '-')}</small>
                </td>
                <td>${escapeHtml(log.period_label || '-')}</td>
                <td>Rp ${formatNumber(log.acquisition_cost)}</td>
                <td>${escapeHtml(log.useful_life_months ?? '-')}</td>
                <td>Rp ${formatNumber(log.depreciation_per_month)}</td>
                <td>${escapeHtml(log.calculated_by_name || '-')}</td>
            `;

            logBody.prepend(row);
            renumberLogRows();
        }

        form.addEventListener('submit', async function (event) {
            event.preventDefault();
            alertBox.classList.add('d-none');

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: new FormData(form)
                });

                const payload = await response.json();

                if (!response.ok) {
                    const firstError = payload.errors ? Object.values(payload.errors)[0][0] : (payload.message || 'Gagal menghitung penyusutan.');
                    setAlert('danger', firstError);
                    return;
                }

                const data = payload.data || {};
                document.getElementById('result-asset-id').textContent = data.asset_id || '-';
                document.getElementById('result-acquisition-cost').textContent = 'Rp ' + formatNumber(data.acquisition_cost);
                document.getElementById('result-useful-life').textContent = data.useful_life_months ?? '-';
                document.getElementById('result-depreciation-per-month').textContent = 'Rp ' + formatNumber(data.depreciation_per_month);
                document.getElementById('result-period').textContent = (data.period_month && data.period_year)
                    ? String(data.period_month).padStart(2, '0') + '/' + data.period_year
                    : '-';
                document.getElementById('result-calculated-at').textContent = data.calculated_at || '-';
                if (data.log_saved && data.log) {
                    prependLogRow(data.log);
                    setAlert('success', payload.message || 'Perhitungan berhasil.');
                } else {
                    setAlert('warning', payload.message || 'Perhitungan berhasil, tetapi log belum tersimpan.');
                }
            } catch (error) {
                setAlert('danger', 'Terjadi kesalahan saat mengirim request.');
            }
        });
    })();
</script>
@endsection