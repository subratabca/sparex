@extends('backend.layout.master')

@section('title', 'Admin || Notification List')

@section('breadcum')
    <span class="text-muted fw-light">Admin /</span> Notification List
@endsection

@section('content')
    @include('backend.components.notification.notification-list')
@endsection