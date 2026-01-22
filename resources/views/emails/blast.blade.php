@extends('emails.layouts.blast')

@section('content')
<p>{!! nl2br(e($messageContent)) !!}</p>
@endsection
