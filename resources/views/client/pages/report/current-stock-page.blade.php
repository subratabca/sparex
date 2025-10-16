@extends('client.layout.master')

@section('title', 'Client || Current Stock')

@section('breadcum')
    <span class="text-muted fw-light">Client /</span> Current Stock
@endsection

@section('content')
    @include('client.components.report.current-stock')
@endsection




