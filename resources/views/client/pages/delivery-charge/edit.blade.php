@extends('client.layout.master')

@section('title', 'Client || Edit Delivery Charge')

@section('breadcum')
    <span class="text-muted fw-light">Client /</span> Update Delivery Charge
@endsection

@section('content')
    @include('client.components.delivery-charge.edit')
@endsection