@extends('backend.layout.master')

@section('title', 'Admin || Product Details')

@section('breadcum')
    <span class="text-muted fw-light">Admin /</span> Product Details
@endsection

@section('content')
    @include('backend.components.product.product-details')
@endsection