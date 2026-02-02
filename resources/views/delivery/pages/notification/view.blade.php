@extends('delivery.layout.master')

@section('title', 'View Notification')

@section('breadcum')
    <span class="text-muted fw-light"></span> View Notification 
@endsection

@section('content')
    @include('delivery.components.notification.view')
@endsection