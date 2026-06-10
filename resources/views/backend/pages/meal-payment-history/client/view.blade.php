@extends('backend.layout.master')

@section('title', 'Meal Payments')

@section('breadcum')
    <span class="text-muted fw-light">Admin /</span> Meal Payments
@endsection

@section('content')
    @include('backend.components.meal-payment-history.client.view')
@endsection