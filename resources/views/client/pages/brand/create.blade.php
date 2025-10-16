@extends('client.layout.master')

@section('title', 'Client || Add Brand')

@section('breadcum')
    <span class="text-muted fw-light">Client /</span> Create New Brand
@endsection

@section('content')
    @include('client.components.brand.create')
@endsection