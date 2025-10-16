@extends('backend.layout.master')

@section('title', 'Admin || Category')

@section('breadcum')
    <span class="text-muted fw-light">Admin /</span> Category List
@endsection

@section('content')
    @include('backend.components.category.index')
    @include('backend.components.category.delete')
@endsection