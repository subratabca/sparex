@extends('backend.layout.master')

@section('title', 'Edit Meal Delivery Charge')

@section('breadcum')
    <span class="text-muted fw-light">Admin /</span> Update Meal Delivery Charge
@endsection

@section('content')
    @include('backend.components.meal-delivery-charge.edit')
@endsection