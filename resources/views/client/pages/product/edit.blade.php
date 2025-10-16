@extends('client.layout.master')

@section('title', 'Client || Edit Product')

@section('breadcum')
    <span class="text-muted fw-light">Client /</span> Update Product
@endsection

@section('content')
    @include('client.components.product.edit')
@endsection