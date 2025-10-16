@extends('client.layout.master')

@section('title', 'Client || Search Orders')

@section('breadcum')
    <span class="text-muted fw-light">Client /</span> Orders By Search
@endsection

@section('content')
    @include('client.components.report.search-order')
@endsection