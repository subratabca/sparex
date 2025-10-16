@extends('backend.layout.master')

@section('title', 'Admin || Todays Order')

@section('breadcum')
    <span class="text-muted fw-light">Admin /</span> Todays Order 
@endsection

@section('content')
    @include('backend.components.report.todays-order')
@endsection