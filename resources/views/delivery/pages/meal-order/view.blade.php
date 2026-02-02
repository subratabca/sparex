@extends('delivery.layout.master')

@section('title', 'Meal Delivery Details')

@section('breadcum')
    <span class="text-muted fw-light"></span>Meal Delivery Details
@endsection

@section('content')
    @include('delivery.components.meal-order.view')
@endsection