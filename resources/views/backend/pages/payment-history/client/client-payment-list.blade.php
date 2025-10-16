@extends('backend.layout.master')

@section('title', 'Admin || Client Payment History')

@section('breadcum')
    <span class="text-muted fw-light">Admin /</span> Client Payment History
@endsection

@section('content')
    @include('backend.components.payment-history.client.client-payment-list')
@endsection