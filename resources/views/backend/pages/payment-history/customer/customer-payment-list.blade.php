@extends('backend.layout.master')

@section('title', 'Admin || Customer Payment History')

@section('breadcum')
    <span class="text-muted fw-light">Admin /</span> Customer Payment History
@endsection

@section('content')
    @include('backend.components.payment-history.customer.customer-payment-list')
@endsection