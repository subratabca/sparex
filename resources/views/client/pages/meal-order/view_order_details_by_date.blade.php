@extends('client.layout.master')

@section('title', 'View Meal Order')

@section('breadcum')
    <span class="text-muted fw-light">Client /</span> View Meal Orders By Date
@endsection

@section('content')
    @include('client.components.meal-order.view_order_details_by_date')
@endsection