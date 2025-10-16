@extends('backend.layout.master')

@section('title', 'Admin || Add Product')

@section('breadcum')
    <span class="text-muted fw-light">Admin /</span> Create New Product
@endsection

@section('content')
   @include('backend.components.product.create')
@endsection