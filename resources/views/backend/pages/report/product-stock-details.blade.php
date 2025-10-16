@extends('backend.layout.master')

@section('title', 'Admin || Stock Details')

@section('breadcum')
    <span class="text-muted fw-light">Admin /</span> Stock Details
@endsection

@section('content')
    @include('backend.components.report.product-stock-details')
@endsection