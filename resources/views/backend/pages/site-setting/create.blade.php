@extends('backend.layout.master')

@section('title', 'Admin || Setting')

@section('breadcum')
    <span class="text-muted fw-light">Admin /</span> Create Site Setting
@endsection

@section('content')
    @include('backend.components.site-setting.create')
@endsection