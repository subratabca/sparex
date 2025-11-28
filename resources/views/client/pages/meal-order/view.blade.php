@extends('client.layout.master')

@section('title', 'View Meal Order')

@section('breadcum')
    <span class="text-muted fw-light">Client /</span> View Meal Order
@endsection

@section('content')
    @include('client.components.meal-order.view')
@endsection