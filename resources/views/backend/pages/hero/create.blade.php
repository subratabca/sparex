@extends('backend.layout.master')

@section('title', 'Admin || Create Hero')

@section('breadcum')
    <span class="text-muted fw-light">Admin /</span> Create Hero Information
@endsection

@section('content')
    @include('backend.components.hero.create')
@endsection