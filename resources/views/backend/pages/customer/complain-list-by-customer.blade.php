@extends('backend.layout.master')

@section('title', 'Admin || Complain List')

@section('breadcum')
    <span class="text-muted fw-light">Admin /</span> Complain List By Customer
@endsection

@section('content')
    @include('backend.components.customer.complain-list-by-customer')
@endsection