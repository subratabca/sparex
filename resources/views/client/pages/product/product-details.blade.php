@extends('client.layout.master')

@section('title', 'Client || Product Details')

@section('breadcum')
    <span class="text-muted fw-light">Client /</span> Product Details
@endsection

@section('content')
    @include('client.components.product.product-details')
@endsection