@extends('backend.layout.master')

@section('title', 'Meal Delivery Charge')

@section('breadcum')
    <span class="text-muted fw-light">Admin /</span> Meal Delivery Charge
@endsection

@section('content')
    @include('backend.components.meal-delivery-charge.index')
    @include('backend.components.meal-delivery-charge.delete')
@endsection