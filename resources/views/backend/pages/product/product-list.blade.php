@extends('backend.layout.master')

@section('title', 'Admn || Product List')

@section('breadcum')
    <span class="text-muted fw-light">Admin /</span> Product List
@endsection

@section('content')
    @include('backend.components.product.product-list')
    @include('backend.components.product.delete')
@endsection
