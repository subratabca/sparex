@extends('client.layout.master')

@section('title', 'Client || Todays Order')

@section('breadcum')
    <span class="text-muted fw-light">Client /</span> Todays Order
@endsection

@section('content')
    @include('client.components.report.todays-order')
@endsection