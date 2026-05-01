@extends('emails.layouts.blast')

@section('content')
<p>{!! nl2br(e($messageContent)) !!}</p>
@if(!empty($trackingPixelUrl))
<img src="{{ $trackingPixelUrl }}" alt="" width="1" height="1" style="display:block;border:0;opacity:0;">
@endif
@endsection
