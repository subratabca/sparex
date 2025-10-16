@extends('backend.layout.master')

@section('title', 'Admin || Customer Payment Details')

@section('breadcum')
    <span class="text-muted fw-light">Admin /</span> Customer Payment Details Information
@endsection

@section('content')
    @include('backend.components.payment-history.customer.customer-payment-details')
@endsection