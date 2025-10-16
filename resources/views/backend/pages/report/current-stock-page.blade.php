@extends('backend.layout.master')

@section('title', 'Admin || Current Stock')

@section('breadcum')
    <span class="text-muted fw-light">Admin /</span> Current Stock
@endsection

@section('content')
    @include('backend.components.report.current-stock')
@endsection