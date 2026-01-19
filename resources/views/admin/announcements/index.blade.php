@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h3>Announcement</h3>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="/admin/announcements">
        @csrf

        <div class="mb-3">
            <label class="form-label">Title</label>
            <input
                type="text"
                name="title"
                class="form-control"
                required
            >
        </div>

        <div class="mb-3">
            <label class="form-label">Message</label>
            <textarea
                name="message"
                class="form-control"
                rows="4"
                required
            ></textarea>
        </div>

        <button class="btn btn-primary">
            Submit Announcement
        </button>
    </form>
</div>
@endsection
