@extends('backend.layout.master')

@section('title', 'Admin || Food List')

@section('breadcum')
    <span class="text-muted fw-light">Admin /</span> Product List By Client
@endsection

@section('content')
    @include('backend.components.client.product-list-by-client')
@endsection