@extends('layouts.app')

@section('title', 'WhatsApp Blast')

@section('content')
<div class="container" style="max-width:900px">

    <h3 class="mb-4">WhatsApp Blast</h3>

    <form method="POST" action="{{ route('admin.blast.whatsapp.send') }}" enctype="multipart/form-data">
        @csrf

        {{-- MODE --}}
        <div class="mb-3">
            <label class="fw-bold">Mode</label><br>
            <label>
                <input type="radio" name="mode" value="db" checked>
                Dari Database
            </label>
            &nbsp;&nbsp;
            <label>
                <input type="radio" name="mode" value="manual">
                Manual Nomor
            </label>
        </div>

        {{-- SEARCH --}}
        <div class="mb-2" id="db-box">
            <input type="text" id="searchBox" class="form-control"
                placeholder="Cari nama siswa, kelas, atau wali...">
        </div>

        {{-- RECIPIENT LIST --}}
        <div class="border rounded p-3 mb-3" style="max-height:260px; overflow:auto" id="recipientList">
            <div class="text-muted">Memuat data...</div>
        </div>

        {{-- MANUAL --}}
        <div class="mb-3 d-none" id="manual-box">
            <label class="fw-bold">Nomor WhatsApp</label>
            <input type="text" id="manualInput" class="form-control"
                placeholder="628xxx, pisahkan dengan koma">
        </div>

        {{-- TEMPLATE --}}
        <div class="mb-3">
            <label class="fw-bold">Template (Opsional)</label>
            <select id="templateSelect" class="form-select">
                <option value="">— Tanpa Template —</option>
            </select>
        </div>

        {{-- MESSAGE --}}
        <div class="mb-3">
            <label class="fw-bold">Pesan</label>
            <textarea name="message" id="messageBox" rows="5"
                class="form-control" required></textarea>
            <small class="text-muted">
                Placeholder:
                <code>{nama_siswa}</code>,
                <code>{kelas}</code>,
                <code>{nama_wali}</code>
            </small>
        </div>

        {{-- ATTACH --}}
        <div class="mb-3">
            <label class="fw-bold">Lampiran</label>
            <input type="file" name="attachments[]" multiple class="form-control">
        </div>

        <input type="hidden" name="targets" id="targetsField">

        <button class="btn btn-primary">
            Kirim WhatsApp Blast
        </button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', async () => {

    const list = document.getElementById('recipientList');
    const search = document.getElementById('searchBox');
    const targetsField = document.getElementById('targetsField');
    const manualBox = document.getElementById('manual-box');
    const dbBox = document.getElementById('db-box');
    const manualInput = document.getElementById('manualInput');

    let recipients = [];

    /* MODE */
    document.querySelectorAll('input[name="mode"]').forEach(r => {
        r.addEventListener('change', () => {
            const isManual = r.value === 'manual' && r.checked;
            manualBox.classList.toggle('d-none', !isManual);
            dbBox.classList.toggle('d-none', isManual);
            list.classList.toggle('d-none', isManual);
        });
    });

    /* LOAD RECIPIENT */
    try {
        const res = await fetch('/admin/blast/recipients?channel=whatsapp');
        recipients = await res.json();
        render(recipients);
    } catch {
        list.innerHTML = '<div class="text-danger">Gagal memuat penerima</div>';
    }

    function render(data) {
        list.innerHTML = '';
        data.forEach(r => {
            list.innerHTML += `
                <div class="form-check mb-2">
                    <input class="form-check-input recipient-check"
                        type="checkbox" value="${r.wa}">
                    <label class="form-check-label">
                        <strong>${r.nama_siswa}</strong> (${r.kelas})<br>
                        <small>${r.nama_wali} — ${r.wa}</small>
                    </label>
                </div>
            `;
        });
    }

    /* SEARCH */
    search.addEventListener('input', () => {
        const q = search.value.toLowerCase();
        render(
            recipients.filter(r =>
                r.nama_siswa.toLowerCase().includes(q) ||
                r.nama_wali.toLowerCase().includes(q) ||
                r.kelas.toLowerCase().includes(q)
            )
        );
    });

    /* SUBMIT */
    document.querySelector('form').addEventListener('submit', () => {

        if (!manualBox.classList.contains('d-none')) {
            targetsField.value = manualInput.value;
            return;
        }

        const numbers = [];
        document.querySelectorAll('.recipient-check:checked')
            .forEach(c => numbers.push(c.value));

        targetsField.value = numbers.join(',');
    });

});
</script>
@endsection