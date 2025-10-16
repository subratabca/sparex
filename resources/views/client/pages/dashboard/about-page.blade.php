@extends('backend.layout.master')
@section('title', 'Admin || About Page')
@section('breadcum', 'About Page Information')
@section('content')
    @include('backend.components.about.index')
    @include('backend.components.about.create')
    @include('backend.components.about.update')
    @include('backend.components.about.delete')
@endsection