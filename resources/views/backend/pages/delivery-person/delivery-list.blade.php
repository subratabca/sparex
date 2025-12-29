@extends('backend.layout.master')

@section('title', 'Delivery List')

@section('breadcum')
    <span class="text-muted fw-light">Admin /</span> Delivery List
@endsection

@section('content')
    @include('backend.components.delivery-person.delivery-list')
    @include('backend.components.delivery-person.delete')
@endsection