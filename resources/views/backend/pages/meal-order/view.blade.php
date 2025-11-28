@extends('backend.layout.master')

@section('title', 'View Meal Details')

@section('breadcum')
    <span class="text-muted fw-light">Admin /</span> View Meal Details
@endsection

@section('content')
    @include('backend.components.meal-order.view')
@endsection