@extends('layouts.app')

@section('section_name', 'Faktur / Jurnal Finance')

@section('content')
@php
    $filters = $filters ?? [];
@endphp

<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title mb-0">Daftar Faktur / Entri Jurnal</h3>
        <div class="card-tools">
            <a href="{{ route('finance.invoice.create') }}" class="btn btn-sm btn-primary">
                <i class="fas fa-plus mr-1"></i> Baru
            </a>
        </div>
    </div>
    <div class="card-body border-bottom">
        <form method="GET" action="{{ route('finance.invoice.index') }}" class="form-row align-items-end">
            <div class="form-group col-md-3">
                <label for="q">Cari</label>
                <input
                    type="text"
                    name="q"
                    id="q"
                    class="form-control"
                    placeholder="No faktur / jurnal / referensi"
                    value="{{ $filters['q'] ?? '' }}"
                >
            </div>
            <div class="form-group col-md-2">
                <label for="status">Status</label>
                <select name="status" id="status" class="form-control">
                    <option value="ALL" {{ ($filters['status'] ?? 'ALL') === 'ALL' ? 'selected' : '' }}>Semua</option>
                    <option value="DRAFT" {{ ($filters['status'] ?? '') === 'DRAFT' ? 'selected' : '' }}>Draft</option>
                    <option value="POSTED" {{ ($filters['status'] ?? '') === 'POSTED' ? 'selected' : '' }}>Terekam</option>
                    <option value="CANCELLED" {{ ($filters['status'] ?? '') === 'CANCELLED' ? 'selected' : '' }}>Batal</option>
                </select>
            </div>
            <div class="form-group col-md-2">
                <label for="entry_type">Jenis</label>
                <select name="entry_type" id="entry_type" class="form-control">
                    <option value="ALL" {{ ($filters['entry_type'] ?? 'ALL') === 'ALL' ? 'selected' : '' }}>Semua</option>
                    <option value="INCOME" {{ ($filters['entry_type'] ?? '') === 'INCOME' ? 'selected' : '' }}>Pemasukan</option>
                    <option value="EXPENSE" {{ ($filters['entry_type'] ?? '') === 'EXPENSE' ? 'selected' : '' }}>Pengeluaran</option>
                </select>
            </div>
            <div class="form-group col-md-1">
                <label for="month">Bulan</label>
                <select name="month" id="month" class="form-control">
                    <option value="">-</option>
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ (int) ($filters['month'] ?? 0) === $m ? 'selected' : '' }}>
                            {{ sprintf('%02d', $m) }}
                        </option>
                    @endfor
                </select>
            </div>
            <div class="form-group col-md-2">
                <label for="year">Tahun</label>
                <input
                    type="number"
                    name="year"
                    id="year"
                    class="form-control"
                    min="1900"
                    max="2100"
                    value="{{ $filters['year'] ?? '' }}"
                >
            </div>
            <div class="form-group col-md-2">
                <label for="per_page">Per Halaman</label>
                <select name="per_page" id="per_page" class="form-control">
                    @foreach([10, 15, 25, 50, 100] as $size)
                        <option value="{{ $size }}" {{ (int) ($filters['per_page'] ?? 15) === $size ? 'selected' : '' }}>
                            {{ $size }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-12">
                <button type="submit" class="btn btn-primary mr-2">
                    <i class="fas fa-filter mr-1"></i> Terapkan
                </button>
                <a href="{{ route('finance.invoice.index') }}" class="btn btn-default">
                    <i class="fas fa-sync mr-1"></i> Reset
                </a>
            </div>
        </form>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-hover mb-0">
                <thead>
                    <tr>
                        <th style="width: 60px;">#</th>
                        <th>No Faktur</th>
                        <th style="width: 130px;">Tanggal</th>
                        <th style="width: 120px;">Jenis</th>
                        <th>Jurnal</th>
                        <th>Referensi</th>
                        <th style="width: 160px;" class="text-right">Debit</th>
                        <th style="width: 160px;" class="text-right">Kredit</th>
                        <th style="width: 120px;">Status</th>
                        <th style="width: 150px;">Dibuat Oleh</th>
                        <th style="width: 210px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $invoice)
                        <tr>
                            <td>{{ $loop->iteration + (($invoices->currentPage() - 1) * $invoices->perPage()) }}</td>
                            <td>
                                <a href="{{ route('finance.invoice.show', $invoice->id) }}" class="font-weight-bold">
                                    {{ $invoice->invoice_no }}
                                </a>
                            </td>
                            <td>{{ optional($invoice->accounting_date)->format('d/m/Y') ?? '-' }}</td>
                            <td>
                                <span class="badge {{ $invoice->entry_type === 'INCOME' ? 'badge-success' : 'badge-danger' }}">
                                    {{ $invoice->entry_type === 'INCOME' ? 'Pemasukan' : 'Pengeluaran' }}
                                </span>
                            </td>
                            <td>{{ $invoice->journal_name }}</td>
                            <td>{{ $invoice->reference ?: '-' }}</td>
                            <td class="text-right">Rp {{ number_format((float) $invoice->total_debit, 2, ',', '.') }}</td>
                            <td class="text-right">Rp {{ number_format((float) $invoice->total_credit, 2, ',', '.') }}</td>
                            <td>
                                @php
                                    $status = strtoupper((string) $invoice->status);
                                    $statusClass = $status === 'POSTED'
                                        ? 'badge-success'
                                        : ($status === 'CANCELLED' ? 'badge-secondary' : 'badge-warning');
                                @endphp
                                <span class="badge {{ $statusClass }}">{{ $status }}</span>
                            </td>
                            <td>{{ $invoice->creator?->name ?? '-' }}</td>
                            <td>
                                <a href="{{ route('finance.invoice.show', $invoice->id) }}" class="btn btn-sm btn-outline-primary mb-1">
                                    <i class="fas fa-eye mr-1"></i> Detail
                                </a>
                                <a href="{{ route('finance.invoice.edit', $invoice->id) }}" class="btn btn-sm btn-outline-warning mb-1">
                                    <i class="fas fa-edit mr-1"></i> Edit
                                </a>
                                @if($invoice->status !== 'POSTED')
                                    <form
                                        action="{{ route('finance.invoice.destroy', $invoice->id) }}"
                                        method="POST"
                                        class="d-inline"
                                        onsubmit="return confirm('Hapus faktur ini?');"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger mb-1">
                                            <i class="fas fa-trash mr-1"></i> Hapus
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center text-muted py-4">
                                Belum ada data faktur/jurnal.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($invoices->hasPages())
        <div class="card-footer">
            {{ $invoices->links() }}
        </div>
    @endif
</div>
@endsection
