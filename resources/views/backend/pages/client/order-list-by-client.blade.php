@extends('backend.layout.master')

@section('title', 'Admin || Order List')

@section('breadcum')
    <span class="text-muted fw-light">Admin /</span> Order List By Client
@endsection

@section('content')
    @include('backend.components.client.order-list-by-client')
@endsection