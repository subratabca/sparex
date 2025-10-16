@extends('client.layout.master')

@section('title', 'Client || Complain List')

@section('breadcum')
    <span class="text-muted fw-light">Client /</span> Complain List By Customer
@endsection

@section('content')
    @include('client.components.customer.complain-list-by-customer')
@endsection