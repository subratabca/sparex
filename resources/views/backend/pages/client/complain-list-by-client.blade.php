@extends('backend.layout.master')

@section('title', 'Admin || Complain List')

@section('breadcum')
    <span class="text-muted fw-light">Admin /</span> Complain List By Client
@endsection

@section('content')
    @include('backend.components.client.complain-list-by-client')
@endsection