@extends('backend.layout.master')

@section('title', 'Meal Order Details By Date')

@section('breadcum')
    <span class="text-muted fw-light">Admin /</span> Meal Order Details By Date
@endsection

@section('content')
    @include('backend.components.meal-order.view_order_details_by_date')
@endsection