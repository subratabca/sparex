@extends('backend.layout.master')

@section('title', 'Admin || Order List')

@section('breadcum')
    <span class="text-muted fw-light">Admin /</span> Order List By Customer
@endsection

@section('content')
    @include('backend.components.customer.order-list-by-customer')
@endsection