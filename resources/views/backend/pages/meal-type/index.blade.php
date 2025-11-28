@extends('backend.layout.master')

@section('title', 'Meal Types')

@section('breadcum')
    <span class="text-muted fw-light">Admin /</span> Meal Types
@endsection

@section('content')
    @include('backend.components.meal-type.index')
    @include('backend.components.meal-type.delete')
@endsection