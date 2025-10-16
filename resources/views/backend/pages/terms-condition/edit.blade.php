@extends('backend.layout.master')

@section('title', 'Admin || Edit T&C')

@section('breadcum')
    <span class="text-muted fw-light">Admin /</span> Edit T&C Information
@endsection

@section('content')
    @include('backend.components.terms-condition.edit')
@endsection