@extends('delivery.layout.master')

@section('title', 'Vehicle Info')

@section('breadcum')
    <span class="text-muted fw-light">Delivery /</span>Vehicle Info
@endsection

@section('content')
    @include('delivery.components.profile.vehicle')
@endsection