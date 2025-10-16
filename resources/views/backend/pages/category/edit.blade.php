@extends('backend.layout.master')

@section('title', 'Admin || Edit Category')

@section('breadcum')
    <span class="text-muted fw-light">Admin /</span> Update Category
@endsection

@section('content')
    @include('backend.components.category.edit')
@endsection