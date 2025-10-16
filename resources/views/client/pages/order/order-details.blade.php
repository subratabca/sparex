@extends('client.layout.master')

@section('title', 'Client || Order Details')

@section('breadcum')
    <span class="text-muted fw-light">Client /</span> Order Details Information
@endsection

@section('content')
    @include('client.components.order.order-details')
    @include('client.components.order.complain-against-customer')
    @include('client.components.order.banned-customer')
    @include('client.components.order.cancel-modal')
@endsection