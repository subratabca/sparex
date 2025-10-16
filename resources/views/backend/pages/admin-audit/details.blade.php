@extends('backend.layout.master')

@section('title', 'Admin || Audit Details')

@section('breadcum')
    <span class="text-muted fw-light">Admin /</span> Audit Details
@endsection

@section('content')
    @include('backend.components.admin-audit.details')
@endsection