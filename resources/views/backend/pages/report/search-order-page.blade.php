@extends('backend.layout.master')

@section('title', 'Admin || Search Orders')

@section('breadcum')
    <span class="text-muted fw-light">Admin /</span> Orders By Search
@endsection

@section('content')
    @include('backend.components.report.search-order')
@endsection