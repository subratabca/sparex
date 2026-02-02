@extends('delivery.layout.master')

@section('title', 'Notification List')

@section('breadcum')
    <span class="text-muted fw-light"></span> Notification List
@endsection

@section('content')
    @include('delivery.components.notification.index')
@endsection