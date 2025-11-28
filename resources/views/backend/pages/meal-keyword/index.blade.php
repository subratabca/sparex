@extends('backend.layout.master')

@section('title', 'Meal Keyword List')

@section('breadcum')
    <span class="text-muted fw-light">Admin /</span> Meal Keyword List
@endsection

@section('content')
    @include('backend.components.meal-keyword.index')
    @include('backend.components.meal-keyword.delete')
@endsection