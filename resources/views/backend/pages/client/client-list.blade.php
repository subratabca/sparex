@extends('backend.layout.master')

@section('title', 'Admin || Client List')

@section('breadcum')
    <span class="text-muted fw-light">Admin /</span> Client List
@endsection

@section('content')
    @include('backend.components.client.client-list')
    @include('backend.components.client.delete')
@endsection