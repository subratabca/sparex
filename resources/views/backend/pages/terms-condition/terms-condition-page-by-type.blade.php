@extends('backend.layout.master')

@section('title', 'Admin || Terms & Condition Page')

@section('breadcum')
    <span class="text-muted fw-light">Admin /</span> Terms & Condition details information
@endsection

@section('content')
    @include('backend.components.terms-condition.terms-condition-page-by-type')
@endsection