@extends('backend.layout.master')

@section('title', 'Admin || Client Payment Details')

@section('breadcum')
    <span class="text-muted fw-light">Admin /</span> Client Payment Details Information
@endsection

@section('content')
    @include('backend.components.payment-history.client.client-payment-details')
@endsection