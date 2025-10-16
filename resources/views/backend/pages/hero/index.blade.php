@extends('backend.layout.master')

@section('title', 'Admin || Hero')

@section('breadcum')
    <span class="text-muted fw-light">Admin /</span> Hero Information
@endsection

@section('content')
    @include('backend.components.hero.index')
    @include('backend.components.hero.delete')
@endsection