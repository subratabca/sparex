@extends('backend.layout.master')

@section('title', 'Admin || Edit About')

@section('breadcum')
    <span class="text-muted fw-light">Admin /</span> Update About Information
@endsection

@section('content')
    @include('backend.components.about.edit')
@endsection