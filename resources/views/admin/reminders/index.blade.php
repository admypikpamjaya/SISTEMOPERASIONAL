@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h3>Reminder</h3>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="/admin/reminders/send">
        @csrf

        <div class="mb-3">
            <label class="form-label">Reminder Message</label>
            <textarea
                name="message"
                class="form-control"
                rows="4"
                required
            ></textarea>
        </div>

        <button class="btn btn-warning">
            Send Reminder
        </button>
    </form>
</div>
@endsection
