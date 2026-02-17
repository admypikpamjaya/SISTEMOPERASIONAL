@extends('layouts.app')

@section('section_name', 'Preview Laporan Laba Rugi')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-outline card-secondary">
            @php
                $periodLabel = $report->year;
                $hasNoDetailLines = count($report->incomeLines) === 0
                    && count($report->expenseLines) === 0
                    && count($report->depreciationLines) === 0;
                if ($report->reportType === 'DAILY') {
                    $periodLabel = $report->periodDate
                        ? \Carbon\Carbon::parse($report->periodDate)->format('d/m/Y')
                        : sprintf('%02d/%02d/%04d', (int) ($report->day ?? 1), (int) ($report->month ?? 1), $report->year);
                } elseif ($report->reportType === 'MONTHLY') {
                    $periodLabel = sprintf('%02d/%04d', (int) ($report->month ?? 1), $report->year);
                }
            @endphp
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">Preview Dokumen LABA DAN RUGI</h3>
                <div class="btn-group">
                    <a href="{{ route('finance.report.snapshots', ['year' => $report->year]) }}" class="btn btn-sm btn-default">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali
                    </a>
                    <button type="button" class="btn btn-sm btn-success dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-download mr-1"></i> Download
                    </button>
                    <div class="dropdown-menu dropdown-menu-right">
                        <a class="dropdown-item" href="{{ route('finance.report.download', ['id' => $report->reportId, 'format' => 'docx']) }}">
                            <i class="far fa-file-word mr-2"></i> DOCX
                        </a>
                        <a class="dropdown-item" href="{{ route('finance.report.download', ['id' => $report->reportId, 'format' => 'excel']) }}">
                            <i class="far fa-file-excel mr-2"></i> Excel
                        </a>
                        <a class="dropdown-item" href="{{ route('finance.report.download', ['id' => $report->reportId, 'format' => 'pdf']) }}">
                            <i class="far fa-file-pdf mr-2"></i> PDF
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    Ini adalah halaman preview. Jika sudah sesuai, pilih format dokumen lalu download.
                </div>

                @if($hasNoDetailLines)
                    <div class="alert alert-warning">
                        Belum ada detail pemasukan/pengeluaran/penyusutan pada snapshot ini.
                    </div>
                @endif

                <div class="mb-3">
                    <div><strong>Periode:</strong> {{ $periodLabel }}</div>
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
