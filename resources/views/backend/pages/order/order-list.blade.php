@extends('backend.layout.master')

@section('title', 'Admin || Order List')

@section('breadcum')
    <span class="text-muted fw-light">Admin /</span> Order List
@endsection

@section('content')
    @include('backend.components.order.order-list')
    @include('backend.components.order.delete')
@endsection