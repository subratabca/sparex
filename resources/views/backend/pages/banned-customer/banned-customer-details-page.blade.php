@extends('backend.layout.master')

@section('title', 'Admin || Banned Customer Details')

@section('breadcum')
    <span class="text-muted fw-light">Admin /</span> Banned Customer Details Information
@endsection

@section('content')
    @include('backend.components.banned-customer.banned-customer-details')
@endsection