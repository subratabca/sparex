@extends('backend.layout.master')

@section('title', 'Meal Orders')

@section('breadcum')
    <span class="text-muted fw-light">Admin /</span> Meal Orders
@endsection

@section('content')
    @include('backend.components.meal-order.index')
@endsection