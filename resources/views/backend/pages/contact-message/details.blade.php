@extends('backend.layout.master')

@section('title', 'Admin || Contact message details')

@section('breadcum')
    <span class="text-muted fw-light">Admin /</span> Contact message details information
@endsection

@section('content')
    @include('backend.components.contact-message.details')
@endsection