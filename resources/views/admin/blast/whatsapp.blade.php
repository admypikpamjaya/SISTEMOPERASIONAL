@extends('layouts.app')

@section('section_name', 'WhatsApp Blast')

@section('content')
<form
    method="POST"
    action="{{ route('admin.blast.whatsapp.send') }}"
    enctype="multipart/form-data"
>
    @csrf

    <div class="form-group mb-3">
        <label>Targets (comma separated)</label>
        <textarea
            name="targets"
            class="form-control"
            rows="3"
            placeholder="628xxxx, 628xxxx"
            required
        ></textarea>
    </div>

    <div class="form-group mb-3">
        <label>Message</label>
        <textarea
            name="message"
            class="form-control"
            rows="4"
            required
        ></textarea>
    </div>

    <div class="form-group mb-3">
        <label>Attachment (optional)</label>
        <input
            type="file"
            name="attachments[]"
            class="form-control"
            multiple
            accept=".pdf,.jpg,.jpeg,.png"
        >
        <small class="text-muted">
            Max 5MB per file. PDF / Image.
        </small>
    </div>

    <button class="btn btn-success">
        Send WhatsApp Blast
    </button>
</form>
@endsection
