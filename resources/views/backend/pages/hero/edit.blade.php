@extends('backend.layout.master')

@section('title', 'Admin || Edit Hero')

@section('breadcum')
    <span class="text-muted fw-light">Admin /</span> Update Hero Information
@endsection

@section('content')
    @include('backend.components.hero.edit')
@endsection