@extends('backend.layout.master')

@section('title', 'Admin || Create T&C')

@section('breadcum')
    <span class="text-muted fw-light">Admin /</span> Create Terms & Condition
@endsection

@section('content')
    @include('backend.components.terms-condition.create')
@endsection