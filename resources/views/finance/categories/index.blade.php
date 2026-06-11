@extends('layouts.app')

@section('content')
<style>
    .fc-shell { display: grid; gap: 1rem; }
    .fc-header { display:flex; align-items:center; justify-content:space-between; gap:1rem; flex-wrap:wrap; }
    .fc-title { display:flex; align-items:center; gap:.75rem; }
    .fc-title-icon { width:44px; height:44px; border-radius:12px; display:flex; align-items:center; justify-content:center; color:#fff; background:linear-gradient(135deg,#2563eb,#06b6d4); box-shadow:0 10px 24px rgba(37,99,235,.24); }
    .fc-title h1 { margin:0; font-size:1.35rem; font-weight:800; color:var(--app-text,#0f172a); }
    .fc-title p { margin:.12rem 0 0; color:var(--app-text-muted,#64748b); font-size:.86rem; }
    .fc-grid { display:grid; grid-template-columns: minmax(280px, 380px) minmax(0,1fr); gap:1rem; align-items:start; }
    @media(max-width: 991px) { .fc-grid { grid-template-columns:1fr; } }
    .fc-card { background:var(--app-surface,#fff); border:1px solid var(--app-border,rgba(148,163,184,.22)); border-radius:14px; box-shadow:var(--app-shadow,0 10px 30px rgba(15,23,42,.08)); overflow:hidden; }
    .fc-card-head { padding:1rem 1.15rem; border-bottom:1px solid var(--app-border,rgba(148,163,184,.18)); display:flex; align-items:center; justify-content:space-between; gap:.75rem; }
    .fc-card-head h2 { margin:0; font-size:.95rem; font-weight:800; color:var(--app-text,#0f172a); display:flex; align-items:center; gap:.5rem; }
    .fc-body { padding:1.15rem; }
    .fc-field { margin-bottom:.9rem; }
    .fc-label { display:flex; align-items:center; gap:.35rem; margin-bottom:.38rem; font-size:.74rem; text-transform:uppercase; letter-spacing:.06em; font-weight:800; color:var(--app-text-muted,#64748b); }
    .fc-control { width:100%; min-height:40px; border:1px solid var(--app-border,#dbe4f0); border-radius:10px; padding:.55rem .7rem; background:var(--app-surface,#fff); color:var(--app-text,#0f172a); font-weight:600; }
    textarea.fc-control { min-height:110px; resize:vertical; }
    .fc-actions { display:flex; align-items:center; gap:.55rem; flex-wrap:wrap; }
    .fc-btn { display:inline-flex; align-items:center; justify-content:center; gap:.4rem; min-height:40px; padding:.55rem .9rem; border-radius:10px; border:1px solid transparent; font-weight:800; font-size:.82rem; text-decoration:none; cursor:pointer; }
    .fc-btn-primary { color:#fff; background:linear-gradient(135deg,#2563eb,#1d4ed8); box-shadow:0 8px 20px rgba(37,99,235,.25); }
    .fc-btn-muted { color:var(--app-text,#0f172a); background:var(--app-surface-soft,#f8fafc); border-color:var(--app-border,#dbe4f0); }
    .fc-btn-danger { color:#dc2626; background:rgba(220,38,38,.08); border-color:rgba(220,38,38,.2); }
    .fc-filter { display:grid; grid-template-columns:minmax(180px,1fr) 150px auto; gap:.6rem; align-items:end; }
    @media(max-width: 768px) { .fc-filter { grid-template-columns:1fr; } }
    .fc-table { width:100%; border-collapse:collapse; }
    .fc-table th { text-align:left; padding:.8rem .95rem; font-size:.72rem; text-transform:uppercase; letter-spacing:.06em; color:var(--app-text-muted,#64748b); background:var(--app-surface-soft,#f8fafc); border-bottom:1px solid var(--app-border,#e2e8f0); white-space:nowrap; }
    .fc-table td { padding:.85rem .95rem; border-bottom:1px solid var(--app-border,#e2e8f0); color:var(--app-text-soft,#334155); vertical-align:top; }
    .fc-table tr:last-child td { border-bottom:0; }
    .fc-name { font-weight:800; color:var(--app-text,#0f172a); }
    .fc-desc { font-size:.78rem; color:var(--app-text-muted,#64748b); margin-top:.18rem; }
    .fc-badge { display:inline-flex; align-items:center; gap:.3rem; border-radius:999px; padding:.24rem .58rem; font-size:.72rem; font-weight:800; }
    .fc-badge.active { background:rgba(16,185,129,.12); color:#047857; }
    .fc-badge.inactive { background:rgba(148,163,184,.16); color:#475569; }
    .fc-row-actions { display:flex; align-items:center; gap:.4rem; flex-wrap:wrap; }
    .fc-empty { padding:2.4rem 1rem; text-align:center; color:var(--app-text-muted,#64748b); font-weight:700; }
</style>

<div class="fc-shell">
    <div class="fc-header">
        <div class="fc-title">
            <div class="fc-title-icon"><i class="fas fa-tags"></i></div>
            <div>
                <h1>Kategori Finance</h1>
                <p>Kelola kategori dinamis untuk pemisahan data finance per unit atau kebutuhan client.</p>
            </div>
        </div>
        <a href="{{ route('finance.dashboard') }}" class="fc-btn fc-btn-muted">
            <i class="fas fa-arrow-left"></i> Dasbor Keuangan
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Validasi gagal:</strong> {{ $errors->first() }}
        </div>
    @endif

    <div class="fc-grid">
        <div class="fc-card">
            <div class="fc-card-head">
                <h2><i class="fas fa-{{ $editCategory ? 'pen' : 'plus' }}"></i> {{ $editCategory ? 'Edit Kategori' : 'Tambah Kategori' }}</h2>
            </div>
            <div class="fc-body">
                <form method="POST" action="{{ $editCategory ? route('finance.categories.update', $editCategory->id) : route('finance.categories.store') }}">
                    @csrf
                    @if($editCategory)
                        @method('PUT')
                    @endif

                    <div class="fc-field">
                        <label class="fc-label" for="name"><i class="fas fa-tag"></i> Nama</label>
                        <input id="name" name="name" class="fc-control" value="{{ old('name', $editCategory->name ?? '') }}" placeholder="TK, SD, SMP, Yayasan">
                    </div>

                    <div class="fc-field">
                        <label class="fc-label" for="description"><i class="fas fa-align-left"></i> Deskripsi</label>
                        <textarea id="description" name="description" class="fc-control" placeholder="Catatan opsional kategori">{{ old('description', $editCategory->description ?? '') }}</textarea>
                    </div>

                    <div class="fc-field">
                        <label class="fc-label" for="status"><i class="fas fa-toggle-on"></i> Status</label>
                        <select id="status" name="status" class="fc-control">
                            @foreach($statusOptions as $value => $label)
                                <option value="{{ $value }}" {{ old('status', $editCategory->status ?? 'active') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="fc-actions">
                        <button type="submit" class="fc-btn fc-btn-primary">
                            <i class="fas fa-save"></i> {{ $editCategory ? 'Simpan Perubahan' : 'Tambah Kategori' }}
                        </button>
                        @if($editCategory)
                            <a href="{{ route('finance.categories.index') }}" class="fc-btn fc-btn-muted">Batal</a>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <div class="fc-card">
            <div class="fc-card-head">
                <h2><i class="fas fa-list"></i> Daftar Kategori</h2>
            </div>
            <div class="fc-body">
                <form method="GET" action="{{ route('finance.categories.index') }}" class="fc-filter">
                    <div>
                        <label class="fc-label" for="q"><i class="fas fa-search"></i> Cari</label>
                        <input id="q" name="q" class="fc-control" value="{{ $filters['q'] ?? '' }}" placeholder="Cari kategori">
                    </div>
                    <div>
                        <label class="fc-label" for="status-filter"><i class="fas fa-filter"></i> Status</label>
                        <select id="status-filter" name="status" class="fc-control">
                            <option value="all" {{ ($filters['status'] ?? 'all') === 'all' ? 'selected' : '' }}>Semua</option>
                            @foreach($statusOptions as $value => $label)
                                <option value="{{ $value }}" {{ ($filters['status'] ?? 'all') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="fc-actions">
                        <button type="submit" class="fc-btn fc-btn-primary"><i class="fas fa-search"></i> Filter</button>
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <table class="fc-table">
                    <thead>
                        <tr>
                            <th>Kategori</th>
                            <th>Status</th>
                            <th>Dipakai</th>
                            <th>Dibuat Oleh</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                            @php($usage = (int) ($usageCounts[$category->id] ?? 0))
                            <tr>
                                <td>
                                    <div class="fc-name">{{ $category->name }}</div>
                                    @if(!empty($category->description))
                                        <div class="fc-desc">{{ $category->description }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="fc-badge {{ $category->status }}">
                                        <i class="fas fa-circle"></i> {{ $statusOptions[$category->status] ?? $category->status }}
                                    </span>
                                </td>
                                <td>{{ number_format($usage, 0, ',', '.') }} data</td>
                                <td>{{ $category->creator?->name ?? '-' }}</td>
                                <td>
                                    <div class="fc-row-actions">
                                        <a href="{{ route('finance.categories.index', array_merge(request()->query(), ['edit' => $category->id])) }}" class="fc-btn fc-btn-muted">
                                            <i class="fas fa-pen"></i> Edit
                                        </a>
                                        <form method="POST" action="{{ route('finance.categories.destroy', $category->id) }}" onsubmit="return confirm('Hapus atau nonaktifkan kategori ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="fc-btn fc-btn-danger">
                                                <i class="fas fa-trash"></i> {{ $usage > 0 ? 'Nonaktifkan' : 'Hapus' }}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <div class="fc-empty">Belum ada kategori finance.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-3">
                {{ $categories->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
