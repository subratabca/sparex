@extends('client.layout.master')

@section('title', 'Client || Complaint List')

@section('breadcum')
    <span class="text-muted fw-light">Client /</span> Complaint List
@endsection

@section('content')
    @include('client.components.complaint.complaint-list')
    @include('client.components.complaint.reply')
@endsection