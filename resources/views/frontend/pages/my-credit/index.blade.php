@extends('frontend.layout.app')
@section('title', 'Credit Info')
@section('content')
    @include('frontend.components.my-credit.index')
    @include('frontend.components.favourite-meal.credit-modal')
@endsection