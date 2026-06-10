@extends('delivery.layout.master')

@section('title', 'My Reports')

@section('breadcum')
    <span class="text-muted fw-light"></span>My Reports
@endsection

@section('content')
    @include('delivery.components.meal-reports.index')
@endsection