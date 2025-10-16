@extends('client.layout.master')

@section('title', 'Client || Banned Customer')

@section('breadcum')
    <span class="text-muted fw-light">Client /</span> Banned Customer List
@endsection

@section('content')
    @include('client.components.banned-customer.banned-customer-list')
    @include('client.components.banned-customer.delete')
@endsection