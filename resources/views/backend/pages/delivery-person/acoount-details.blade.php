@extends('backend.layout.master')

@section('title', 'Account Details')

@section('breadcum')
    <span class="text-muted fw-light">Admin /</span> Delivery Person Account Details
@endsection

@section('content')
    @include('backend.components.delivery-person.acoount-details')
@endsection