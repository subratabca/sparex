@extends('frontend.layout.app')
@section('title', 'User || Menu List')
@section('content')
    @include('frontend.components.customer-menu.index')
    @include('frontend.components.customer-menu.delete')
@endsection