@extends('backend.layout.master')

@section('title', 'Admin || Create About')

@section('breadcum')
    <span class="text-muted fw-light">Admin /</span> Create About Information
@endsection

@section('content')
    @include('backend.components.about.create')
@endsection