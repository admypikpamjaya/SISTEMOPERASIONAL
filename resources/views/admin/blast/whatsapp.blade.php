@extends('layouts.app')

@section('title', 'WhatsApp Blast')

@section('content')

<style>
    .wa-wrapper {
        max-width: 1100px;
        margin: auto;
    }

    /* ===== HEADER ===== */
    .wa-header {
        background: linear-gradient(135deg,#25D366,#128C7E);
        border-radius: 20px;
        padding: 28px;
        color: white;
        display: flex;
        align-items: center;
        gap: 20px;
        margin-bottom: 30px;
        box-shadow: 0 15px 35px rgba(18,140,126,.25);
    }

    .wa-logo {
        width: 70px;
        height: 70px;
        background: white;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .wa-logo svg {
        width: 40px;
        height: 40px;
        fill: #25D366;
    }

    .wa-title {
        font-size: 24px;
        font-weight: 700;
    }

    .wa-subtitle {
        font-size: 14px;
        opacity: .9;
    }

    /* ===== CARD ===== */
    .wa-card {
        background: white;
        border-radius: 18px;
        padding: 22px;
        margin-bottom: 24px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 8px 24px rgba(0,0,0,.05);
    }

    .section-title {
        font-weight: 600;
        font-size: 15px;
        margin-bottom: 16px;
        color: #1f2937;
    }

    /* ===== RECIPIENT ===== */
    .recipient-box {
        max-height: 300px;
        overflow-y: auto;
    }

    .recipient-item {
        padding: 12px 14px;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        margin-bottom: 10px;
        transition: .2s;
    }

    .recipient-item:hover {
        background: #f0fdf4;
        border-color: #25D366;
    }

    .recipient-name {
        font-weight: 600;
        font-size: 14px;
    }

    .recipient-detail {
        font-size: 12px;
        color: #6b7280;
    }

    .select-all-wrapper {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px solid #e5e7eb;
    }

    .btn-select {
        background: #ecfdf5;
        border: 1px solid #25D366;
        color: #128C7E;
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 12px;
        cursor: pointer;
        transition: .2s;
    }

    .btn-select:hover {
        background: #25D366;
        color: white;
    }

    /* ===== FORM ===== */
    .form-label-modern {
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 6px;
        display: block;
    }

    .form-control-modern {
        border-radius: 10px;
        border: 1px solid #d1d5db;
        padding: 10px 12px;
        font-size: 14px;
    }

    .form-control-modern:focus {
        border-color: #25D366;
        box-shadow: 0 0 0 3px rgba(37,211,102,.15);
    }

    /* ===== BUTTON ===== */
    .submit-btn {
        background: linear-gradient(135deg,#25D366,#128C7E);
        border: none;
        color: white;
        padding: 14px 28px;
        border-radius: 14px;
        font-weight: 600;
        transition: .2s;
    }

    .submit-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 28px rgba(18,140,126,.35);
    }

</style>

<div class="container py-4 wa-wrapper">

    {{-- HEADER --}}
    <div class="wa-header">
        <div class="wa-logo">
            {{-- WhatsApp SVG --}}
            <svg viewBox="0 0 24 24">
                <path d="M12 2C6.48 2 2 6.29 2 11.6c0 2.05.66 3.95 1.79 5.52L2 22l5.08-1.64A10.11 10.11 0 0012 21.2c5.52 0 10-4.29 10-9.6S17.52 2 12 2zm.04 17.45c-1.62 0-3.2-.44-4.57-1.26l-.33-.2-3.02.97.99-2.94-.21-.3a7.88 7.88 0 01-1.3-4.12c0-4.42 3.77-8.02 8.4-8.02 4.63 0 8.4 3.6 8.4 8.02 0 4.43-3.77 8.02-8.4 8.02z"/>
            </svg>
        </div>
        <div>
            <div class="wa-title">WhatsApp Blast</div>
            <div class="wa-subtitle">
                Kirim pesan massal dengan cepat dan terstruktur
            </div>
        </div>
    </div>

    <form method="POST"
          action="{{ route('admin.blast.whatsapp.send') }}"
          enctype="multipart/form-data">
        @csrf

        {{-- RECIPIENT SECTION --}}
        <div class="wa-card">
            <div class="section-title">
                Pilih Penerima
            </div>

            <div class="select-all-wrapper">
                <div>Total Penerima: {{ $recipients->count() }}</div>
                <button type="button" class="btn-select" id="selectAllBtn">
                    Select All
                </button>
            </div>

            <div class="recipient-box">

                @forelse($recipients as $r)
                    <div class="recipient-item">
                        <div class="form-check">
                            <input class="form-check-input recipient-checkbox"
                                   type="checkbox"
                                   name="recipient_ids[]"
                                   value="{{ $r->id }}"
                                   id="r{{ $r->id }}">

                            <label class="form-check-label w-100"
                                   for="r{{ $r->id }}">
                                <div class="recipient-name">
                                    {{ $r->nama_siswa }} ({{ $r->kelas }})
                                </div>
                                <div class="recipient-detail">
                                    {{ $r->nama_wali }} — {{ $r->wa_wali }}
                                </div>
                            </label>
                        </div>
                    </div>
                @empty
                    <p class="text-muted">Tidak ada data penerima.</p>
                @endforelse

            </div>
        </div>

        {{-- TEMPLATE --}}
        <div class="wa-card">
            <div class="section-title">Template (Optional)</div>
            <select name="template_id" class="form-control form-control-modern">
                <option value="">-- Tanpa Template --</option>
                @foreach($templates as $t)
                    <option value="{{ $t->id }}">
                        {{ $t->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- GLOBAL MESSAGE --}}
        <div class="wa-card">
            <div class="section-title">Pesan Global (Optional)</div>
            <textarea name="message"
                      class="form-control form-control-modern"
                      rows="3"
                      placeholder="Pesan default untuk semua penerima"></textarea>
        </div>

        {{-- ATTACHMENT --}}
        <div class="wa-card">
            <div class="section-title">Lampiran (Optional)</div>
            <input type="file"
                   name="attachments[]"
                   class="form-control form-control-modern"
                   multiple>
        </div>

        <div class="text-end">
            <button type="submit" class="submit-btn">
                Kirim WhatsApp Blast
            </button>
        </div>

    </form>

</div>

<script>
    const selectAllBtn = document.getElementById('selectAllBtn');
    const checkboxes = document.querySelectorAll('.recipient-checkbox');

    let allSelected = false;

    selectAllBtn.addEventListener('click', function() {
        allSelected = !allSelected;

        checkboxes.forEach(cb => cb.checked = allSelected);

        selectAllBtn.textContent = allSelected ? 'Unselect All' : 'Select All';
    });
</script>

@endsection
