@extends('layouts.app')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

    :root {
        --fs-blue: #1d4ed8;
        --fs-blue-dark: #1e3a8a;
        --fs-bg: #f0f4fd;
        --fs-card: #ffffff;
        --fs-text: #0f172a;
        --fs-muted: #64748b;
        --fs-border: rgba(37, 99, 235, 0.10);
        --fs-radius: 18px;
    }

    body, .content-wrapper {
        background: var(--fs-bg) !important;
        font-family: 'Plus Jakarta Sans', sans-serif !important;
    }

    .ac-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1.5rem;
    }
    .ac-title {
        display: flex;
        align-items: center;
        gap: 0.9rem;
    }
    .ac-title-icon {
        width: 50px;
        height: 50px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, var(--fs-blue), var(--fs-blue-dark));
        color: #fff;
        font-size: 1.2rem;
    }
    .ac-title h1 {
        margin: 0;
        font-size: 1.4rem;
        font-weight: 800;
        color: var(--fs-text);
    }
    .ac-title p {
        margin: 0;
        color: var(--fs-muted);
        font-size: 0.82rem;
    }

    .ac-card {
        background: var(--fs-card);
        border: 1px solid var(--fs-border);
        border-radius: var(--fs-radius);
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .ac-table {
        width: 100%;
        border-collapse: collapse;
    }
    .ac-table th, .ac-table td {
        padding: 0.75rem 1rem;
        border: 1px solid var(--fs-border);
        text-align: right;
    }
    .ac-table th {
        background: #f8fafc;
        font-weight: 700;
        text-align: center;
    }
    .ac-table td.label-col {
        text-align: left;
        font-weight: 600;
    }
    .text-danger { color: #dc2626 !important; }
    .text-success { color: #059669 !important; }
</style>

<div class="ac-header">
    <div class="ac-title">
        <div class="ac-title-icon"><i class="fas fa-balance-scale-right"></i></div>
        <div>
            <h1>Komparasi Audit Keuangan</h1>
            <p>Membandingkan data laporan keuangan Sebelum (Pre-Audit) vs Sesudah Audit (Audited)</p>
        </div>
    </div>
    <div>
        <a href="{{ route('finance.report.balance-sheet') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
</div>

<div class="ac-card">
    <form method="GET" action="{{ route('finance.report.audit-comparison') }}" class="row">
        <div class="col-md-3">
            <label class="form-label">Jenis Laporan</label>
            <select name="statement_type" class="form-control" onchange="this.form.submit()">
                <option value="BALANCE_SHEET" {{ $statementType === 'BALANCE_SHEET' ? 'selected' : '' }}>Neraca Saldo (Balance Sheet)</option>
                <option value="PROFIT_LOSS" {{ $statementType === 'PROFIT_LOSS' ? 'selected' : '' }}>Laba Rugi (Profit & Loss)</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Kategori</label>
            <select name="category_id" class="form-control" onchange="this.form.submit()">
                <option value="">Semua Kategori</option>
                @foreach($financeCategoryOptions as $cat)
                    <option value="{{ $cat->id }}" {{ $dto->categoryId == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
    </form>
</div>

@if(!$preAuditBatch && !$auditedBatch)
    <div class="ac-card text-center text-muted">
        <i class="fas fa-info-circle fa-2x mb-2"></i>
        <p>Tidak ada data laporan Pre-Audit maupun Audited yang ditemukan.</p>
    </div>
@else
    <div class="ac-card">
        <table class="ac-table">
            <thead>
                <tr>
                    <th rowspan="2" class="label-col">Komponen Laporan</th>
                    <th colspan="2">Pre-Audit</th>
                    <th colspan="2">Audited</th>
                    <th rowspan="2">Varians (Selisih)</th>
                </tr>
                <tr>
                    <th style="font-size: 0.75rem; color: #64748b;">
                        {{ $preAuditBatch ? $preAuditBatch->batch_name : 'Tidak Ada Data' }}
                    </th>
                    <th>Nilai</th>
                    <th style="font-size: 0.75rem; color: #64748b;">
                        {{ $auditedBatch ? $auditedBatch->batch_name : 'Tidak Ada Data' }}
                    </th>
                    <th>Nilai</th>
                </tr>
            </thead>
            <tbody>
                @if($statementType === 'BALANCE_SHEET')
                    @php
                        $preSections = collect($preAuditData['sections'] ?? [])->keyBy('key');
                        $audSections = collect($auditedData['sections'] ?? [])->keyBy('key');
                        $keys = ['kas', 'piutang', 'aset', 'liabilitas'];
                    @endphp
                    @foreach($keys as $key)
                        @php
                            $preTotal = (float)($preSections[$key]['total'] ?? 0);
                            $audTotal = (float)($audSections[$key]['total'] ?? 0);
                            $diff = $audTotal - $preTotal;
                        @endphp
                        <tr>
                            <td class="label-col">{{ ucfirst($key) }}</td>
                            <td></td>
                            <td>Rp {{ number_format($preTotal, 2, ',', '.') }}</td>
                            <td></td>
                            <td>Rp {{ number_format($audTotal, 2, ',', '.') }}</td>
                            <td class="{{ $diff < 0 ? 'text-danger' : ($diff > 0 ? 'text-success' : '') }}">
                                Rp {{ number_format($diff, 2, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                    <tr style="background: #f8fafc; font-weight: 700;">
                        <td class="label-col">TOTAL KESEIMBANGAN (Aset & Liabilitas)</td>
                        <td colspan="2">Rp {{ number_format(data_get($preAuditData, 'summary.asset_side_total', 0), 2, ',', '.') }}</td>
                        <td colspan="2">Rp {{ number_format(data_get($auditedData, 'summary.asset_side_total', 0), 2, ',', '.') }}</td>
                        @php
                            $totalDiff = data_get($auditedData, 'summary.asset_side_total', 0) - data_get($preAuditData, 'summary.asset_side_total', 0);
                        @endphp
                        <td class="{{ $totalDiff < 0 ? 'text-danger' : ($totalDiff > 0 ? 'text-success' : '') }}">
                            Rp {{ number_format($totalDiff, 2, ',', '.') }}
                        </td>
                    </tr>
                @else
                    @php
                        $preInc = (float)data_get($preAuditData, 'totals.income', 0);
                        $preExp = (float)data_get($preAuditData, 'totals.expense', 0);
                        $preNet = (float)data_get($preAuditData, 'totals.net_result', 0);
                        
                        $audInc = (float)data_get($auditedData, 'totals.income', 0);
                        $audExp = (float)data_get($auditedData, 'totals.expense', 0);
                        $audNet = (float)data_get($auditedData, 'totals.net_result', 0);
                    @endphp
                    <tr>
                        <td class="label-col">Pendapatan</td>
                        <td></td><td>Rp {{ number_format($preInc, 2, ',', '.') }}</td>
                        <td></td><td>Rp {{ number_format($audInc, 2, ',', '.') }}</td>
                        <td class="{{ ($audInc - $preInc) != 0 ? 'text-success' : '' }}">Rp {{ number_format($audInc - $preInc, 2, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="label-col">Pengeluaran</td>
                        <td></td><td>Rp {{ number_format($preExp, 2, ',', '.') }}</td>
                        <td></td><td>Rp {{ number_format($audExp, 2, ',', '.') }}</td>
                        <td class="{{ ($audExp - $preExp) != 0 ? 'text-danger' : '' }}">Rp {{ number_format($audExp - $preExp, 2, ',', '.') }}</td>
                    </tr>
                    <tr style="background: #f8fafc; font-weight: 700;">
                        <td class="label-col">Laba Bersih</td>
                        <td colspan="2">Rp {{ number_format($preNet, 2, ',', '.') }}</td>
                        <td colspan="2">Rp {{ number_format($audNet, 2, ',', '.') }}</td>
                        <td class="{{ ($audNet - $preNet) < 0 ? 'text-danger' : (($audNet - $preNet) > 0 ? 'text-success' : '') }}">
                            Rp {{ number_format($audNet - $preNet, 2, ',', '.') }}
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
@endif

@endsection
