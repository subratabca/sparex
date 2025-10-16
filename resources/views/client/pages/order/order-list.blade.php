@extends('client.layout.master')

@section('title', 'Client || Order List')

@section('breadcum')
    <span class="text-muted fw-light">Client /</span> Order List
@endsection

@section('content')
    @include('client.components.order.order-list')
    @include('client.components.order.delete')
@endsection