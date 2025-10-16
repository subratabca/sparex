@extends('client.layout.master')

@section('title', 'Client || Brand')

@section('breadcum')
    <span class="text-muted fw-light">Client /</span> Brand List
@endsection

@section('content')
    @include('client.components.brand.index')
    @include('client.components.brand.delete')
@endsection