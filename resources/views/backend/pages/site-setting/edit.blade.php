@extends('backend.layout.master')

@section('title', 'Admin || Edit Setting')

@section('breadcum')
    <span class="text-muted fw-light">Admin /</span> Update Site Setting
@endsection

@section('content')
    @include('backend.components.site-setting.edit')
@endsection