@extends('client.layout.master')

@section('title', 'Client || Banned Customer Details')

@section('breadcum')
    <span class="text-muted fw-light">Client /</span> Banned Customer Details Information
@endsection

@section('content')
    @include('client.components.banned-customer.banned-customer-details')
@endsection