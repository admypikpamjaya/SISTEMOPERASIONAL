@extends('layouts.app')

@section('section_name', 'Asset Depreciation')

@section('content')
@php
    $nowWib = now(config('app.timezone'));
@endphp

<style>
:root {
    --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --success-gradient: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
    --info-gradient: linear-gradient(135deg, #a1c4fd 0%, #c2e9fb 100%);
    --warning-gradient: linear-gradient(135deg, #fad0c4 0%, #ffd1ff 100%);
}

.card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    overflow: hidden;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.15);
}

.card-header {
    background: var(--primary-gradient);
    color: white;
    border-bottom: none;
    padding: 1.25rem 1.5rem;
}

.card-header h3 {
    font-weight: 600;
    font-size: 1.25rem;
    margin: 0;
    letter-spacing: 0.5px;
}

.card-body {
    padding: 1.5rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    font-weight: 500;
    color: #4a5568;
    margin-bottom: 0.5rem;
    font-size: 0.95rem;
    letter-spacing: 0.3px;
}

.form-control {
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    padding: 0.625rem 1rem;
    font-size: 0.95rem;
    transition: all 0.3s ease;
    background: #f8fafc;
}

.form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    background: white;
}

.form-control[type="number"] {
    -moz-appearance: textfield;
}

.form-control[type="number"]::-webkit-outer-spin-button,
.form-control[type="number"]::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

.form-row {
    display: flex;
    gap: 1rem;
}

.form-row > .form-group {
    flex: 1;
}

.btn-primary {
    background: var(--primary-gradient);
    border: none;
    border-radius: 10px;
    padding: 0.75rem 1.5rem;
    font-weight: 500;
    letter-spacing: 0.5px;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
    transition: all 0.3s ease;
    width: 100%;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
}

.btn-primary:active {
    transform: translateY(0);
}

.text-muted {
    color: #718096 !important;
    font-size: 0.85rem;
    margin-top: 0.25rem;
    display: block;
}

/* Result Card Styles */
.result-card {
    background: linear-gradient(135deg, #f6f9fc 0%, #f1f4f8 100%);
}

.result-card .dl-horizontal {
    margin: 0;
}

.result-card dt {
    font-weight: 500;
    color: #4a5568;
    padding: 0.5rem 0;
    border-bottom: 1px dashed #e2e8f0;
}

.result-card dd {
    font-weight: 600;
    color: #2d3748;
    padding: 0.5rem 0;
    border-bottom: 1px dashed #e2e8f0;
}

.result-card .row:last-child dt,
.result-card .row:last-child dd {
    border-bottom: none;
}

.result-value {
    background: white;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    display: inline-block;
    font-weight: 600;
}

/* Table Styles */
.table {
    margin-bottom: 0;
}

.table thead th {
    background: linear-gradient(135deg, #f8fafc 0%, #eef2f6 100%);
    color: #4a5568;
    font-weight: 600;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-top: none;
    padding: 1rem;
    white-space: nowrap;
}

.table tbody td {
    padding: 1rem;
    vertical-align: middle;
    color: #4a5568;
    border-bottom: 1px solid #e2e8f0;
}

.table tbody tr:hover {
    background-color: #f8fafc;
}

.table tbody tr:last-child td {
    border-bottom: none;
}

/* Alert Styles */
.alert {
    border: none;
    border-radius: 10px;
    padding: 1rem 1.25rem;
    margin-bottom: 1.5rem;
    font-weight: 500;
}

.alert-success {
    background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
    color: #22543d;
}

.alert-danger {
    background: linear-gradient(135deg, #feb692 0%, #ea5455 100%);
    color: #742a2a;
}

.alert-warning {
    background: linear-gradient(135deg, #fad0c4 0%, #ffd1ff 100%);
    color: #744210;
}

/* Badge Styles */
.badge {
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-weight: 500;
    font-size: 0.75rem;
}

.badge-primary {
    background: var(--primary-gradient);
    color: white;
}

/* Small Text */
.small.text-muted {
    font-size: 0.75rem;
    color: #a0aec0 !important;
}

/* Loading State */
.btn-loading {
    position: relative;
    pointer-events: none;
    opacity: 0.7;
}

.btn-loading:after {
    content: '';
    position: absolute;
    width: 20px;
    height: 20px;
    top: 50%;
    left: 50%;
    margin-left: -10px;
    margin-top: -10px;
    border: 2px solid white;
    border-top-color: transparent;
    border-radius: 50%;
    animation: spinner 0.6s linear infinite;
}

@keyframes spinner {
    to { transform: rotate(360deg); }
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 3rem !important;
}

.empty-state i {
    font-size: 3rem;
    color: #cbd5e0;
    margin-bottom: 1rem;
}

.empty-state p {
    color: #a0aec0;
    font-size: 1rem;
    margin: 0;
}

/* Responsive */
@media (max-width: 768px) {
    .card-body {
        padding: 1rem;
    }
    
    .form-row {
        flex-direction: column;
        gap: 0;
    }
    
    .table {
        font-size: 0.85rem;
    }
    
    .table td, .table th {
        padding: 0.75rem;
    }
}
</style>

<div class="row">
    <div class="col-lg-6 col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title mb-0">
                    <i class="fas fa-calculator mr-2"></i>
                    Kalkulasi Penyusutan Garis Lurus
                </h3>
            </div>
            <form id="depreciation-form" action="{{ route('finance.depreciation.calc') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <label for="asset_id">
                            <i class="fas fa-barcode mr-1"></i>
                            Asset ID
                        </label>
                        <select id="asset_id" name="asset_id" class="form-control" required>
                            <option value="">Pilih asset dari database</option>
                            @foreach(($assets ?? collect()) as $asset)
                                <option value="{{ $asset->id }}">
                                    #{{ $asset->id }} - {{ $asset->account_code }} ({{ $asset->category }})
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">
                            <i class="fas fa-database mr-1"></i>
                            Total asset tersedia: {{ ($assets ?? collect())->count() }}
                        </small>
                    </div>
                    <div class="form-group">
                        <label for="acquisition_cost">
                            <i class="fas fa-money-bill-wave mr-1"></i>
                            Nilai Perolehan
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">Rp</span>
                            </div>
                            <input type="number" id="acquisition_cost" name="acquisition_cost" class="form-control" min="0" step="0.01" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="useful_life_months">
                            <i class="fas fa-clock mr-1"></i>
                            Umur Manfaat (bulan)
                        </label>
                        <input type="number" id="useful_life_months" name="useful_life_months" class="form-control" min="1" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="month">
                                <i class="fas fa-calendar-alt mr-1"></i>
                                Bulan
                            </label>
                            <select id="month" name="month" class="form-control" required>
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ $nowWib->month === $m ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="year">
                                <i class="fas fa-calendar-check mr-1"></i>
                                Tahun
                            </label>
                            <input type="number" id="year" name="year" class="form-control" min="1900" max="2100" value="{{ $nowWib->year }}" required>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent">
                    <button type="submit" class="btn btn-primary" id="submit-btn">
                        <i class="fas fa-calculator mr-1"></i> 
                        Hitung Penyusutan
                    </button>
                </div>
            </form>
        </div>
    </div>
    <div class="col-lg-6 col-md-12">
        <div class="card result-card">
            <div class="card-header">
                <h3 class="card-title mb-0">
                    <i class="fas fa-chart-line mr-2"></i>
                    Hasil Perhitungan
                </h3>
            </div>
            <div class="card-body">
                <div id="depreciation-alert" class="alert d-none" role="alert"></div>
                <dl class="row mb-0">
                    <dt class="col-sm-5">Asset ID</dt>
                    <dd class="col-sm-7" id="result-asset-id">
                        <span class="badge badge-primary">-</span>
                    </dd>

                    <dt class="col-sm-5">Nilai Perolehan</dt>
                    <dd class="col-sm-7" id="result-acquisition-cost">-</dd>

                    <dt class="col-sm-5">Umur Bulan</dt>
                    <dd class="col-sm-7" id="result-useful-life">
                        <span class="result-value">-</span>
                    </dd>

                    <dt class="col-sm-5">Penyusutan / Bulan</dt>
                    <dd class="col-sm-7" id="result-depreciation-per-month">-</dd>

                    <dt class="col-sm-5">Periode</dt>
                    <dd class="col-sm-7" id="result-period">
                        <span class="result-value">-</span>
                    </dd>

                    <dt class="col-sm-5">Waktu Hitung (WIB)</dt>
                    <dd class="col-sm-7" id="result-calculated-at">
                        <i class="far fa-clock mr-1"></i>
                        <span>-</span>
                    </dd>
                </dl>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card mt-3">
            <div class="card-header">
                <h3 class="card-title mb-0">
                    <i class="fas fa-history mr-2"></i>
                    Log Kalkulasi Penyusutan
                </h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
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
                                    <td><span class="badge badge-primary">{{ $index + 1 }}</span></td>
                                    <td>{{ $log->calculated_at?->timezone(config('app.timezone'))->format('d/m/Y H:i:s') ?? '-' }}</td>
                                    <td>
                                        <div><strong>{{ $log->asset?->account_code ?? '-' }}</strong></div>
                                        <small class="text-muted">ID: {{ $log->asset_id }}</small>
                                    </td>
                                    <td><span class="badge badge-primary">{{ sprintf('%02d/%04d', (int) $log->period_month, (int) $log->period_year) }}</span></td>
                                    <td>Rp {{ number_format((float) $log->acquisition_cost, 2, ',', '.') }}</td>
                                    <td>{{ (int) $log->useful_life_months }}</td>
                                    <td>Rp {{ number_format((float) $log->depreciation_per_month, 2, ',', '.') }}</td>
                                    <td>{{ $log->calculator?->name ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr id="depreciation-log-empty-row">
                                    <td colspan="8" class="empty-state">
                                        <i class="fas fa-calculator"></i>
                                        <p>Belum ada log kalkulasi penyusutan.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    (function () {
        const form = document.getElementById('depreciation-form');
        const submitBtn = document.getElementById('submit-btn');
        const alertBox = document.getElementById('depreciation-alert');
        const logBody = document.getElementById('depreciation-log-body');

        function setAlert(type, message) {
            alertBox.className = 'alert alert-' + type;
            alertBox.textContent = message;
            alertBox.classList.remove('d-none');
            
            // Auto hide alert after 5 seconds for success messages
            if (type === 'success') {
                setTimeout(() => {
                    alertBox.classList.add('d-none');
                }, 5000);
            }
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
                const badge = row.querySelector('td:first-child .badge');
                if (badge) {
                    badge.textContent = String(index + 1);
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
                <td><span class="badge badge-primary">1</span></td>
                <td>${escapeHtml(log.calculated_at_label || '-')}</td>
                <td>
                    <div><strong>${escapeHtml(log.asset_account_code || '-')}</strong></div>
                    <small class="text-muted">ID: ${escapeHtml(log.asset_id || '-')}</small>
                </td>
                <td><span class="badge badge-primary">${escapeHtml(log.period_label || '-')}</span></td>
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
            
            // Add loading state to button
            submitBtn.classList.add('btn-loading');
            submitBtn.disabled = true;

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
                
                // Update result display
                document.getElementById('result-asset-id').innerHTML = `<span class="badge badge-primary">${data.asset_id || '-'}</span>`;
                document.getElementById('result-acquisition-cost').textContent = 'Rp ' + formatNumber(data.acquisition_cost);
                document.getElementById('result-useful-life').innerHTML = `<span class="result-value">${data.useful_life_months ?? '-'}</span>`;
                document.getElementById('result-depreciation-per-month').textContent = 'Rp ' + formatNumber(data.depreciation_per_month);
                
                const periodLabel = (data.period_month && data.period_year)
                    ? String(data.period_month).padStart(2, '0') + '/' + data.period_year
                    : '-';
                document.getElementById('result-period').innerHTML = `<span class="result-value">${periodLabel}</span>`;
                
                const timeElement = document.getElementById('result-calculated-at');
                timeElement.innerHTML = `<i class="far fa-clock mr-1"></i><span>${data.calculated_at || '-'}</span>`;
                
                if (data.log_saved && data.log) {
                    prependLogRow(data.log);
                    setAlert('success', payload.message || 'Perhitungan berhasil.');
                } else {
                    setAlert('warning', payload.message || 'Perhitungan berhasil, tetapi log belum tersimpan.');
                }
            } catch (error) {
                setAlert('danger', 'Terjadi kesalahan saat mengirim request.');
                console.error('Error:', error);
            } finally {
                // Remove loading state
                submitBtn.classList.remove('btn-loading');
                submitBtn.disabled = false;
            }
        });

        // Add input validation
        document.getElementById('acquisition_cost').addEventListener('input', function(e) {
            if (this.value < 0) this.value = 0;
        });

        document.getElementById('useful_life_months').addEventListener('input', function(e) {
            if (this.value < 1) this.value = 1;
        });
    })();
</script>
@endsection