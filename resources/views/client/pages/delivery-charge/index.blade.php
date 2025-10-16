@extends('client.layout.master')

@section('title', 'Client || Delivery Charge')

@section('breadcum')
    <span class="text-muted fw-light">Client /</span> Delivery Charge List
@endsection

@section('content')
    @include('client.components.delivery-charge.index')
    @include('client.components.delivery-charge.delete')
@endsection