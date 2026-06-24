@extends('frontend.layout.app')
@section('title', 'Meal Plan Setup')
@section('content')
    @include('frontend.components.meal-plan-setup.index')
    @include('frontend.components.meal-plan.credit-modal')
@endsection
