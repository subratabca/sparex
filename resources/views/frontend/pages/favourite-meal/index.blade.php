@extends('frontend.layout.app')
@section('title', 'Search Favourite Meal')
@section('content')
    @include('frontend.components.favourite-meal.index')
    @include('frontend.components.favourite-meal.credit-modal')
@endsection