@extends('client.layout.master')

@section('title', 'Client || Complaint Details')

@section('breadcum')
    <span class="text-muted fw-light">Client /</span> Complaint Details Information
@endsection

@section('content')
    @include('client.components.complaint.complaint-details')
    @include('client.components.complaint.reply')
@endsection