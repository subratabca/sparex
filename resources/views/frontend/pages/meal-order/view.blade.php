@extends('frontend.layout.app')
@section('title', 'View Meal Order')
@section('content')
    @include('frontend.components..meal-order.view')
    @include('frontend.components..meal-order.delete-meal-item')
@endsection