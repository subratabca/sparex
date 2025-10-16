@extends('client.layout.master')

@section('title', 'Client || Edit Brand')

@section('breadcum')
    <span class="text-muted fw-light">Client /</span> Update Brand
@endsection

@section('content')
    @include('client.components.brand.edit')
@endsection