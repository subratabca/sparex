@extends('backend.layout.master')

@section('title', 'Create Meal Type')

@section('breadcum')
    <span class="text-muted fw-light">Admin /</span> Create Meal Type
@endsection

@section('content')
    @include('backend.components.meal-type.create')
@endsection