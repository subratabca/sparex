@extends('backend.layout.master')

@section('title', 'Admin || Customer Complain List')

@section('breadcum')
    <span class="text-muted fw-light">Admin /</span> Customer Complain List
@endsection

@section('content')
    @include('backend.components.customer-complain.customer-complain-list')
    @include('backend.components.customer-complain.delete')
@endsection