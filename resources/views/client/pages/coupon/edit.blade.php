@extends('client.layout.master')

@section('title', 'Client || Edit Coupon')

@section('breadcum')
    <span class="text-muted fw-light">Client /</span> Update Coupon
@endsection

@section('content')
    @include('client.components.coupon.edit')
@endsection