@extends('backend.layout.master')

@section('title', 'Admin || Order Details')

@section('breadcum')
    <span class="text-muted fw-light">Admin /</span> Order Details Information
@endsection

@section('content')
    @include('backend.components.order.order-details')
@endsection