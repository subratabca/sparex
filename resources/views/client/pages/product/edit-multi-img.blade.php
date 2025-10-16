@extends('client.layout.master')

@section('title', 'Client || Edit Product Img')

@section('breadcum')
    <span class="text-muted fw-light">Client /</span> Update Product Image
@endsection

@section('content')
    @include('client.components.product.edit-multi-img')
@endsection