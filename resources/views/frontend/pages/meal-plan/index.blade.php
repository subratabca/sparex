@extends('frontend.layout.app')
@section('title', 'Meal Settings')
@section('content')
    @include('frontend.components.meal-plan.index')
    @include('frontend.components.meal-plan.credit-modal')
@endsection