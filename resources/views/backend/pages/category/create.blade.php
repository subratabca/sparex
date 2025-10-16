@extends('backend.layout.master')

@section('title', 'Admin || Create Category')

@section('breadcum')
    <span class="text-muted fw-light">Admin /</span> Create Category
@endsection

@section('content')
    @include('backend.components.category.create')
@endsection