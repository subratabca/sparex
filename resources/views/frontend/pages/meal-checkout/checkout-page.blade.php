@extends('frontend.layout.app')
@section('title', 'Meal Checkout')
@section('content')
    @include('frontend.components.meal-checkout.checkout')
    @include('frontend.components.favourite-meal.credit-modal')
@endsection