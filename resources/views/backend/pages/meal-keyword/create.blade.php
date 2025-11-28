@extends('backend.layout.master')

@section('title', 'Create Meal Keyword')

@section('breadcum')
    <span class="text-muted fw-light">Admin /</span> Create Meal Keyword
@endsection

@section('content')
    @include('backend.components.meal-keyword.create')
@endsection