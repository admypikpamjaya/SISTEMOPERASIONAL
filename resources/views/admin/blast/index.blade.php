@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-body">
        <h4>Blast Message</h4>

        <a href="{{ route('admin.blast.whatsapp') }}" class="btn btn-success">
            Blast WhatsApp
        </a>

        <a href="{{ route('admin.blast.email') }}" class="btn btn-primary ml-2">
            Blast Email
        </a>
    </div>
</div>
@endsection
