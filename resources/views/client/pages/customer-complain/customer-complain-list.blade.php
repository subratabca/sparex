@extends('client.layout.master')

@section('title', 'Client || Customer Complain List')

@section('breadcum')
    <span class="text-muted fw-light">Client /</span> Customer Complain List
@endsection

@section('content')
    @include('client.components.customer-complain.customer-complain-list')
    @include('client.components.customer-complain.delete')
@endsection