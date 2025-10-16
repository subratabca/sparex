@extends('client.layout.master')

@section('title', 'Client || Notification List')

@section('breadcum')
    <span class="text-muted fw-light">Client /</span> Notification List
@endsection

@section('content')
    @include('client.components.notification.notification-list')
@endsection