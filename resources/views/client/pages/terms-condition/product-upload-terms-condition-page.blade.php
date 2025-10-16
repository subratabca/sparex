@extends('client.layout.master')

@section('title', 'Product Upload T&C')

@section('breadcum')
    <span class="text-muted fw-light">Client /</span> Product Upload T&C 
@endsection

@section('content')
    @include('client.components.terms-condition.product-upload-terms-condition')
@endsection