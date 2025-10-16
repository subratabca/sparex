@extends('backend.layout.master')

@section('title', 'Admin || Setting')

@section('breadcum')
    <span class="text-muted fw-light">Admin /</span> Site Setting Information
@endsection

@section('content')
    @include('backend.components.site-setting.index')
    @include('backend.components.site-setting.delete')
@endsection