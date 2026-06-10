@extends('backend.layout.master')

@section('title', 'Delivery Payment Details')

@section('breadcum')
    <span class="text-muted fw-light">Admin /</span> Delivery Payment Details
@endsection

@section('content')
    @include('backend.components.meal-payment-history.delivery.view')
@endsection