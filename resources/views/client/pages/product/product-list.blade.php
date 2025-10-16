@extends('client.layout.master')

@section('title', 'Client || Product List')

@section('breadcum')
    <span class="text-muted fw-light">Client /</span> Products List
@endsection

@section('content')
    @include('client.components.product.product-list')
    @include('client.components.product.delete')
@endsection
