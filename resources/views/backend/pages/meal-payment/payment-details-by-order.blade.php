@extends('backend.layout.master')

@section('title', 'Meal Payments By Order')

@section('breadcum')
    <span class="text-muted fw-light">Admin /</span> Meal Payments By Order
@endsection

@section('content')
    @include('backend.components.meal-payment.payment-details-by-order')
@endsection