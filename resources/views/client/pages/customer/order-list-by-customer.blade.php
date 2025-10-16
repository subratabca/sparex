@extends('client.layout.master')

@section('title', 'Client || Order List')

@section('breadcum')
    <span class="text-muted fw-light">Client /</span> Order List By Customer
@endsection

@section('content')
    @include('client.components.customer.order-list-by-customer')
@endsection