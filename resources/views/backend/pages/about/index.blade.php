@extends('backend.layout.master')

@section('title', 'Admin || About')

@section('breadcum')
    <span class="text-muted fw-light">Admin /</span> About Information
@endsection

@section('content')
    @include('backend.components.about.index')
    @include('backend.components.about.delete')
@endsection