@extends('backend.layout.master')

@section('title', 'Admin || Edit Product Img')

@section('breadcum')
    <span class="text-muted fw-light">Admin /</span> Update Product Image
@endsection

@section('content')
    @include('backend.components.product.edit-multi-img')
@endsection