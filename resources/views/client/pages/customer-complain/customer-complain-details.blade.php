@extends('client.layout.master')

@section('title', 'Client || Customer Complain Details')

@section('breadcum')
    <span class="text-muted fw-light">Client /</span> Customer Complain Details
@endsection

@section('content')
    @include('client.components.customer-complain.customer-complain-details')
@endsection