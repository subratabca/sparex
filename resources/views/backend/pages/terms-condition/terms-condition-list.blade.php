@extends('backend.layout.master')

@section('title', 'Admin || Terms & Condition List')

@section('breadcum')
    <span class="text-muted fw-light">Admin /</span> Terms & Condition List
@endsection

@section('content')
    @include('backend.components.terms-condition.terms-condition-list')
    @include('backend.components.terms-condition.delete')
@endsection