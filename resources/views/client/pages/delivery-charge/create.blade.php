@extends('client.layout.master')

@section('title', 'Client || Add Delivery Charge')

@section('breadcum')
    <span class="text-muted fw-light">Client /</span> Create New Delivery Charge
@endsection

@section('content')
    @include('client.components.delivery-charge.create')
@endsection