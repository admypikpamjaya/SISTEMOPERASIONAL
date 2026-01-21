@extends('layouts.app')

@section('section_name', 'Email Blast')

@section('content')
<div class="card">
    <div class="card-body">

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <form
            method="POST"
            action="{{ route('admin.blast.email.send') }}"
            enctype="multipart/form-data"
        >
            @csrf

            <div class="form-group mb-3">
                <label>Targets (comma separated)</label>
                <textarea
                    name="targets"
                    class="form-control"
                    rows="2"
                    placeholder="user1@mail.com, user2@mail.com"
                    required
                ></textarea>
            </div>

            <div class="form-group mb-3">
                <label>Subject</label>
                <input
                    name="subject"
                    class="form-control"
                    required
                >
            </div>

            <div class="form-group mb-3">
                <label>Message</label>
                <textarea
                    name="message"
                    class="form-control"
                    rows="6"
                    required
                ></textarea>
            </div>

            <div class="form-group mb-3">
                <label>Attachments (optional)</label>
                <input
                    type="file"
                    name="attachments[]"
                    class="form-control"
                    multiple
                >
            </div>

            <button class="btn btn-primary">
                Send Email Blast
            </button>
        </form>

    </div>
</div>
@endsection
