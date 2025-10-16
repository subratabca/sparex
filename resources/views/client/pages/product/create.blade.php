@extends('client.layout.master')

@section('title', 'Client || Add Product')

@section('breadcum')
    <span class="text-muted fw-light">Client /</span> Create New Product
@endsection

@section('content')
    @include('client.components.product.create')
@endsection