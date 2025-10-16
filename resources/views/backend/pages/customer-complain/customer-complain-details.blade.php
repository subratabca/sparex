@extends('backend.layout.master')

@section('title', 'Admin || Customer Complain Details')

@section('breadcum')
    <span class="text-muted fw-light">Admin /</span> Customer Complain Details
@endsection

@section('content')
    @include('backend.components.customer-complain.customer-complain-details')
    @include('backend.components.customer-complain.complain-feedback-by-admin')
@endsection