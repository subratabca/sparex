@extends('client.layout.master')

@section('title', 'Client || Customer Details')

@section('breadcum')
    <span class="text-muted fw-light">Client /</span> Customer Details Information
@endsection

@section('content')
    @include('client.components.customer.customer-details')
@endsection