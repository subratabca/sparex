@extends('client.layout.master')

@section('title', 'Meal Order List')

@section('breadcum')
    <span class="text-muted fw-light">Client /</span> Meal Order List
@endsection

@section('content')
    @include('client.components.meal-order.index')
@endsection