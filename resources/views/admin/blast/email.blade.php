@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h3>Blast</h3>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="/admin/blast/send">
        @csrf

        <div class="mb-3">
            <label class="form-label">Blast Content</label>
            <textarea
                name="content"
                class="form-control"
                rows="4"
                required
            ></textarea>
        </div>

        <button class="btn btn-danger">
            Send Blast
        </button>
    </form>
</div>
@endsection