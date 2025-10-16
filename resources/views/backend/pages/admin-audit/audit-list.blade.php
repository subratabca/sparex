@extends('backend.layout.master')

@section('title', 'Admin || Audit List')

@section('breadcum')
    <span class="text-muted fw-light">Admin /</span> Audit List
@endsection

@section('content')
    @include('backend.components.admin-audit.audit-list')
    @include('backend.components.admin-audit.delete')
@endsection