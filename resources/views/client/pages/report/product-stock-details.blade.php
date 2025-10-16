@extends('client.layout.master')

@section('title', 'Client || Stock Details')

@section('breadcum')
    <span class="text-muted fw-light">Client /</span> Stock Details
@endsection

@section('content')
    @include('client.components.report.product-stock-details')
@endsection