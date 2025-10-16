@extends('backend.layout.master')

@section('title', 'Admin || Edit Product')

@section('breadcum')
    <span class="text-muted fw-light">Admin /</span> Update Product
@endsection

@section('content')
    @include('backend.components.product.edit')
@endsection