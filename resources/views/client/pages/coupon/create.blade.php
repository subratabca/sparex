@extends('client.layout.master')

@section('title', 'Client || Add Coupon')

@section('breadcum')
    <span class="text-muted fw-light">Client /</span> Create New Coupon
@endsection

@section('content')
    @include('client.components.coupon.create')
@endsection