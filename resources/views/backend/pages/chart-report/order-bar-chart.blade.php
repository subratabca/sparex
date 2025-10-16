@extends('backend.layout.master')

@section('title', 'Admin || Orders by chart')

@section('breadcum')
    <span class="text-muted fw-light">Admin /</span> Orders by chart
@endsection

@section('content')
    @include('backend.components.chart-report.order-bar-chart')
@endsection