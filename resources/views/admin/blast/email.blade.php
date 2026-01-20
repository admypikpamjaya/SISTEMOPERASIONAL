@extends('layouts.app')

@section('content')
<form method="POST" action="{{ route('admin.blast.email.send') }}">
    @csrf

    <div class="form-group">
        <label>Targets (comma separated)</label>
        <textarea name="targets" class="form-control"></textarea>
    </div>

    <div class="form-group">
        <label>Subject</label>
        <input name="subject" class="form-control">
    </div>

    <div class="form-group">
        <label>Message</label>
        <textarea name="message" class="form-control"></textarea>
    </div>

    <button class="btn btn-primary">Send Email Blast</button>
</form>
@endsection
