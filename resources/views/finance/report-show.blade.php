@extends('layouts.app')

@section('section_name', 'Preview Laporan Laba Rugi')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-outline card-secondary">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">Preview Dokumen LABA DAN RUGI</h3>
                <div class="btn-group">
                    <a href="{{ route('finance.report.snapshots', ['year' => $report->year]) }}" class="btn btn-sm btn-default">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali
                    </a>
                    <a href="{{ route('finance.report.download', $report->reportId) }}" class="btn btn-sm btn-success">
                        <i class="fas fa-download mr-1"></i> Download Document
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    Ini adalah halaman preview. Jika sudah sesuai, klik <strong>Download Document</strong>.
                </div>

                <div class="mb-3">
                    <div><strong>Periode:</strong> {{ $report->month ? sprintf('%02d', $report->month) . '/' . $report->year : $report->year }}</div>
                    <div><strong>Jenis:</strong> {{ $report->reportType }}</div>
                    <div><strong>Saldo Awal:</strong> Rp {{ number_format($report->openingBalance, 2, ',', '.') }}</div>
                    <div><strong>Saldo Akhir:</strong> Rp {{ number_format($report->endingBalance, 2, ',', '.') }}</div>
                    <div><strong>Disusun Oleh:</strong> {{ $report->generatedByName ?? '-' }}</div>
                    <div><strong>Generated At:</strong> {{ $report->generatedAt->format('Y-m-d H:i:s') }}</div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 140px;">Kode</th>
                                <th style="width: 260px;">Uraian</th>
                                <th>Keterangan</th>
                                <th style="width: 220px;" class="text-right">Nominal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="bg-light">
                                <td colspan="4"><strong>Penghasilan</strong></td>
                            </tr>
                            @forelse($report->incomeLines as $line)
                                <tr>
                                    <td>{{ $line->lineCode }}</td>
                                    <td>{{ $line->lineLabel }}</td>
                                    <td>{{ $line->description ?: '-' }}</td>
                                    <td class="text-right">Rp {{ number_format($line->amount, 2, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Tidak ada item penghasilan.</td>
                                </tr>
                            @endforelse
                            <tr class="bg-light">
                                <td colspan="3"><strong>Total Penghasilan</strong></td>
                                <td class="text-right"><strong>Rp {{ number_format($report->totalIncome, 2, ',', '.') }}</strong></td>
                            </tr>

                            <tr class="bg-light">
                                <td colspan="4"><strong>Pengeluaran</strong></td>
                            </tr>
                            @forelse($report->expenseLines as $line)
                                <tr>
                                    <td>{{ $line->lineCode }}</td>
                                    <td>{{ $line->lineLabel }}</td>
                                    <td>{{ $line->description ?: '-' }}</td>
                                    <td class="text-right">Rp {{ number_format($line->amount, 2, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Tidak ada item pengeluaran.</td>
                                </tr>
                            @endforelse
                            <tr class="bg-light">
                                <td colspan="3"><strong>Total Pengeluaran (non-penyusutan)</strong></td>
                                <td class="text-right"><strong>Rp {{ number_format($report->totalExpense, 2, ',', '.') }}</strong></td>
                            </tr>

                            <tr class="bg-light">
                                <td colspan="4"><strong>Penyusutan</strong></td>
                            </tr>
                            @forelse($report->depreciationLines as $line)
                                <tr>
                                    <td>{{ $line->lineCode }}</td>
                                    <td>{{ $line->lineLabel }}</td>
                                    <td>{{ $line->description ?: '-' }}</td>
                                    <td class="text-right">Rp {{ number_format($line->amount, 2, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Tidak ada item penyusutan.</td>
                                </tr>
                            @endforelse
                            <tr class="bg-light">
                                <td colspan="3"><strong>Penyusutan</strong></td>
                                <td class="text-right"><strong>Rp {{ number_format($report->totalDepreciation, 2, ',', '.') }}</strong></td>
                            </tr>
                            <tr class="bg-success">
                                <td colspan="3"><strong>Surplus (Defisit)</strong></td>
                                <td class="text-right"><strong>Rp {{ number_format($report->surplusDeficit, 2, ',', '.') }}</strong></td>
                            </tr>
                            <tr class="bg-info">
                                <td colspan="3"><strong>Saldo Akhir</strong></td>
                                <td class="text-right"><strong>Rp {{ number_format($report->endingBalance, 2, ',', '.') }}</strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
