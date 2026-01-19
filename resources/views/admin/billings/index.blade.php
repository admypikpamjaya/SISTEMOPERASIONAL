@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h3>Billing</h3>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="/admin/billings/1/confirm">
        @csrf

        <button class="btn btn-success">
            Confirm Billing #1
        </button>
    </form>
</div>
@endsection
