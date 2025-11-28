@extends('backend.layout.master')

@section('title', 'Add Meal Delivery Charge')

@section('breadcum')
    <span class="text-muted fw-light">Admin /</span> Add Meal Delivery Charge
@endsection

@section('content')
    @include('backend.components.meal-delivery-charge.create')
@endsection