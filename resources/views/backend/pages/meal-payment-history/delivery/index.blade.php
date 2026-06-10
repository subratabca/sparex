@extends('backend.layout.master')

@section('title', 'Delivery Payment List')

@section('breadcum')
    <span class="text-muted fw-light">Admin /</span> Delivery Payment List
@endsection

@section('content')
    @include('backend.components.meal-payment-history.delivery.index')
    @include('backend.components.meal-payment-history.delivery.credit-modal')
@endsection