@extends('client.layout.master')

@section('title', 'Client || Document Verify')

@section('breadcum')
    <span class="text-muted fw-light">Client /</span> Client Document Verification
@endsection

@section('content')
    @include('client.components.profile.client-document')
@endsection