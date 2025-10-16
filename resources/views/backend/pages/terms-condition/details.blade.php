@extends('backend.layout.master')

@section('title', 'Admin || T&C Details')

@section('breadcum')
    <span class="text-muted fw-light">Admin /</span> T&C Details Information
@endsection

@section('content')
    @include('backend.components.terms-condition.details')
@endsection