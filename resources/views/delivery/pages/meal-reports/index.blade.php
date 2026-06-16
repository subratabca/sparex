@extends('delivery.layout.master')

@section('title', 'Dashboard || Report Summary')

@section('breadcum')
    <span class="text-muted fw-light">Rider /</span>Reports
@endsection

@section('content')
    @include('delivery.components.meal-reports.index')
@endsection