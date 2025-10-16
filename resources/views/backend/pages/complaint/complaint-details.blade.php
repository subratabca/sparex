@extends('backend.layout.master')

@section('title', 'Admin || Complaint Details')

@section('breadcum')
    <span class="text-muted fw-light">Admin /</span> Complaint Details Information
@endsection

@section('content')
    @include('backend.components.complaint.complaint-details')
    @include('backend.components.complaint.complaint-solved-investigation-modal')
@endsection

