@extends('client.layout.master')

@section('title', 'Client || Coupon List')

@section('breadcum')
    <span class="text-muted fw-light">Client /</span> Coupon List
@endsection

@section('content')
    @include('client.components.coupon.index')
    @include('client.components.coupon.delete')
@endsection