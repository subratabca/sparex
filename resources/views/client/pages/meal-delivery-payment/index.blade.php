@extends('client.layout.master')

@section('title', 'Delivery Payment List')

@section('breadcum')
    <span class="text-muted fw-light">Client /</span> Delivery Payment List
@endsection

@section('content')
    @include('client.components.meal-delivery-payment.index')
    @include('client.components.meal-delivery-payment.credit-modal')
@endsection