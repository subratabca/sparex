@extends('backend.layout.master')
@section('title', 'Admin || Site Setting Page')
@section('breadcum', 'Site Setting')
@section('content')
    @include('backend.components.site-setting.index')
    @include('backend.components.site-setting.create')
    @include('backend.components.site-setting.update')
    @include('backend.components.site-setting.delete')
@endsection