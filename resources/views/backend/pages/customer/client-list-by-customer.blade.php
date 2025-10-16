@extends('backend.layout.master')

@section('title', 'Admin || Client List')

@section('breadcum')
    <span class="text-muted fw-light">Admin /</span> Client List By Customer
@endsection

@section('content')
    @include('backend.components.customer.client-list-by-customer')
@endsection