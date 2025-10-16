@extends('backend.layout.master')

@section('title', 'Admin || Contact message List')

@section('breadcum')
    <span class="text-muted fw-light">Admin /</span> Contact message List
@endsection

@section('content')
    @include('backend.components.contact-message.contact-message-list')
    @include('backend.components.contact-message.delete')
@endsection