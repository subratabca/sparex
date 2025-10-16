@extends('client.layout.master')

@section('title', 'Client Details')

@section('breadcum')
    <span class="text-muted fw-light">Client /</span> Client Details Information
@endsection

@section('content')
    @include('client.components.profile.client-details')
@endsection