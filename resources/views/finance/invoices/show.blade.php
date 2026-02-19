@extends('layouts.app')

@section('section_name', 'Detail Faktur / Jurnal')

@section('content')
@php
    $activeTab = $errors->has('note') ? 'notes' : 'items';
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
        <h3 class="card-title mb-0">{{ $invoice->invoice_no }}</h3>
        <div class="btn-group">
            <a href="{{ route('finance.invoice.index') }}" class="btn btn-sm btn-default">
                <i class="fas fa-arrow-left mr-1"></i> Kembali
            </a>
            <a href="{{ route('finance.invoice.edit', $invoice->id) }}" class="btn btn-sm btn-warning">
                <i class="fas fa-edit mr-1"></i> Edit
            </a>
            @if($invoice->status === 'DRAFT')
                <form action="{{ route('finance.invoice.post', $invoice->id) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="fas fa-check mr-1"></i> Rekam
                    </button>
                </form>
                <form
                    action="{{ route('finance.invoice.destroy', $invoice->id) }}"
                    method="POST"
                    class="d-inline"
                    onsubmit="return confirm('Hapus faktur ini?');"
                >
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger">
                        <i class="fas fa-trash mr-1"></i> Hapus
                    </button>
                </form>
            @elseif($invoice->status === 'POSTED')
                <form action="{{ route('finance.invoice.set-draft', $invoice->id) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-warning">
                        <i class="fas fa-undo mr-1"></i> Reset ke Rancangan
                    </button>
                </form>
            @endif
        </div>
    </div>
    <div class="card-body">
        <div class="row mb-2">
            <div class="col-md-6">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <th style="width: 180px;">Nomor Faktur</th>
                        <td>{{ $invoice->invoice_no }}</td>
                    </tr>
                    <tr>
                        <th>Tanggal Akuntansi</th>
                        <td>{{ optional($invoice->accounting_date)->format('d/m/Y') ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Jenis</th>
                        <td>{{ $invoice->entry_type === 'INCOME' ? 'Pemasukan' : 'Pengeluaran' }}</td>
                    </tr>
                    <tr>
                        <th>Jurnal</th>
                        <td>{{ $invoice->journal_name }}</td>
                    </tr>
                    <tr>
                        <th>Referensi</th>
                        <td>{{ $invoice->reference ?: '-' }}</td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <th style="width: 180px;">Status</th>
                        <td>
                            @php
                                $status = strtoupper((string) $invoice->status);
                                $statusClass = $status === 'POSTED'
                                    ? 'badge-success'
                                    : ($status === 'CANCELLED' ? 'badge-secondary' : 'badge-warning');
                            @endphp
                            <span class="badge {{ $statusClass }}">{{ $status }}</span>
                        </td>
                    </tr>
                    <tr>
                        <th>Total Debit</th>
                        <td>Rp {{ number_format((float) $invoice->total_debit, 2, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <th>Total Kredit</th>
                        <td>Rp {{ number_format((float) $invoice->total_credit, 2, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <th>Dibuat Oleh</th>
                        <td>{{ $invoice->creator?->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Terekam Oleh</th>
                        <td>
                            {{ $invoice->poster?->name ?? '-' }}
                            @if($invoice->posted_at)
                                <small class="text-muted d-block">{{ $invoice->posted_at->format('d/m/Y H:i:s') }}</small>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <ul class="nav nav-tabs" id="invoiceTab" role="tablist">
            <li class="nav-item">
                <a class="nav-link {{ $activeTab === 'items' ? 'active' : '' }}" id="items-tab" data-toggle="tab" href="#items" role="tab" aria-controls="items" aria-selected="{{ $activeTab === 'items' ? 'true' : 'false' }}">
                    Item Jurnal
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $activeTab === 'notes' ? 'active' : '' }}" id="notes-tab" data-toggle="tab" href="#notes" role="tab" aria-controls="notes" aria-selected="{{ $activeTab === 'notes' ? 'true' : 'false' }}">
                    Log Catatan ({{ $invoice->notes->count() }})
                </a>
            </li>
        </ul>
        <div class="tab-content border border-top-0 p-3" id="invoiceTabContent">
            <div class="tab-pane fade {{ $activeTab === 'items' ? 'show active' : '' }}" id="items" role="tabpanel" aria-labelledby="items-tab">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm mb-0">
                        <thead>
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th style="width: 140px;">Asset Category</th>
                                <th style="width: 120px;">Akun</th>
                                <th style="width: 150px;">Rekanan</th>
                                <th>Label</th>
                                <th style="width: 200px;">Analisa Distribusi</th>
                                <th style="width: 160px;" class="text-right">Debit</th>
                                <th style="width: 160px;" class="text-right">Kredit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($invoice->items as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $item->asset_category ?: '-' }}</td>
                                    <td>{{ $item->account_code }}</td>
                                    <td>{{ $item->partner_name ?: '-' }}</td>
                                    <td>{{ $item->label }}</td>
                                    <td>{{ $item->analytic_distribution ?: '-' }}</td>
                                    <td class="text-right">Rp {{ number_format((float) $item->debit, 2, ',', '.') }}</td>
                                    <td class="text-right">Rp {{ number_format((float) $item->credit, 2, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted">Belum ada item jurnal.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="6" class="text-right">Total</th>
                                <th class="text-right">Rp {{ number_format((float) $invoice->total_debit, 2, ',', '.') }}</th>
                                <th class="text-right">Rp {{ number_format((float) $invoice->total_credit, 2, ',', '.') }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="tab-pane fade {{ $activeTab === 'notes' ? 'show active' : '' }}" id="notes" role="tabpanel" aria-labelledby="notes-tab">
                <form method="POST" action="{{ route('finance.invoice.notes.store', $invoice->id) }}" class="mb-3">
                    @csrf
                    <div class="form-group">
                        <label for="note">Tambahkan Catatan</label>
                        <textarea
                            name="note"
                            id="note"
                            rows="3"
                            class="form-control"
                            placeholder="Finance atau IT Support bisa menuliskan catatan tindak lanjut di sini."
                            required
                        >{{ old('note') }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="fas fa-comment-dots mr-1"></i> Simpan Catatan
                    </button>
                </form>

                <div class="list-group">
                    @forelse($invoice->notes as $note)
                        <div class="list-group-item">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">
                                    {{ $note->user?->name ?? 'System' }}
                                    <small class="text-muted">({{ $note->user?->role ?? '-' }})</small>
                                </h6>
                                <small class="text-muted">{{ optional($note->created_at)->format('d/m/Y H:i:s') }}</small>
                            </div>
                            <p class="mb-0">{{ $note->note }}</p>
                        </div>
                    @empty
                        <div class="text-muted">Belum ada catatan pada faktur ini.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
