@extends('delivery.layout.master')

@section('title', 'Meal Delivery List')

@section('breadcum')
    <span class="text-muted fw-light"></span>Meal Delivery List
@endsection

@section('content')
    @include('delivery.components.meal-order.index')
@endsection