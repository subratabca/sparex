@extends('backend.layout.master')

@section('title', 'Admin || Complaint List')

@section('breadcum')
    <span class="text-muted fw-light">Admin /</span> Complaint List
@endsection

@section('content')
    @include('backend.components.complaint.complaint-list')
    @include('backend.components.complaint.delete')
@endsection