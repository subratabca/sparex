@extends('backend.layout.master')

@section('title', 'Admin || Customer List')

@section('breadcum')
    <span class="text-muted fw-light">Admin /</span> Customer List By Client
@endsection

@section('content')
    @include('backend.components.client.customer-list-by-client')
@endsection