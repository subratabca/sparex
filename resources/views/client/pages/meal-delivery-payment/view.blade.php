@extends('client.layout.master')

@section('title', 'Delivery Payment Details')

@section('breadcum')
    <span class="text-muted fw-light">Client /</span> Delivery Payment Details
@endsection

@section('content')
    @include('client.components.meal-delivery-payment.view')
@endsection