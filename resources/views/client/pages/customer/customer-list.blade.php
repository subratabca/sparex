@extends('client.layout.master')

@section('title', 'Client || Customer List')

@section('breadcum')
    <span class="text-muted fw-light">Client /</span> Customer List
@endsection

@section('content')
    @include('client.components.customer.customer-list')
    @include('client.components.customer.complain-against-customer-modal')
    @include('client.components.customer.banned-customer-modal')
@endsection