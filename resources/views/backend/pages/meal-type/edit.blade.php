@extends('backend.layout.master')

@section('title', 'Edit Meal Type')

@section('breadcum')
    <span class="text-muted fw-light">Admin /</span> Update Edit Meal Type
@endsection

@section('content')
    @include('backend.components.meal-type.edit')
@endsection