@extends('backend.layout.master')

@section('title', 'Admin || Customer List')

@section('breadcum')
    <span class="text-muted fw-light">Admin /</span> Customer List
@endsection

@section('content')
    @include('backend.components.customer.customer-list')
    @include('backend.components.customer.delete')
@endsection
