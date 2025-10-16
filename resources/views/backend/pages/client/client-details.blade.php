@extends('backend.layout.master')

@section('title', 'Admin || Client Details')

@section('breadcum')
    <span class="text-muted fw-light">Admin /</span> Client Details Information
@endsection

@section('content')
    @include('backend.components.client.client-details')
@endsection