@extends('backend.layout.master')

@section('title', 'Admin || Customer Details')

@section('breadcum')
    <span class="text-muted fw-light">Admin /</span> Customer Details Information
@endsection

@section('content')
    @include('backend.components.customer.customer-details')
@endsection