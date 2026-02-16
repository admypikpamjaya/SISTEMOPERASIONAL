@extends('layouts.app')

@section('section_name', 'Asset Depreciation')

@section('content')
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
                        <input type="text" id="asset_id" name="asset_id" class="form-control" placeholder="Masukkan ID aset" required>
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
                                    <option value="{{ $m }}">{{ $m }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="year">Tahun</label>
                            <input type="number" id="year" name="year" class="form-control" min="1900" max="2100" value="{{ now()->year }}" required>
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
                </dl>
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
                document.getElementById('result-acquisition-cost').textContent = formatNumber(data.acquisition_cost);
                document.getElementById('result-useful-life').textContent = data.useful_life_months ?? '-';
                document.getElementById('result-depreciation-per-month').textContent = formatNumber(data.depreciation_per_month);
                setAlert('success', payload.message || 'Perhitungan berhasil.');
            } catch (error) {
                setAlert('danger', 'Terjadi kesalahan saat mengirim request.');
            }
        });
    })();
</script>
@endsection
