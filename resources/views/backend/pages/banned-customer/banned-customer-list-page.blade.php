@extends('backend.layout.master')

@section('title', 'Admin || Banned Customer')

@section('breadcum')
    <span class="text-muted fw-light">Admin /</span> Create About Information
@endsection

@section('content')
    @include('backend.components.banned-customer.banned-customer-list')
@endsection