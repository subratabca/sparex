@extends('backend.layout.master')

@section('title', 'Edit Meal Keyword')

@section('breadcum')
    <span class="text-muted fw-light">Admin /</span> Update Meal Keyword
@endsection

@section('content')
    @include('backend.components.meal-keyword.edit')
@endsection