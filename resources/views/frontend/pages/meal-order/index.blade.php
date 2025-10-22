@extends('frontend.layout.app')
@section('title', 'User || Meal Order List')
@section('content')
    @include('frontend.components.meal-order.index')
    @include('frontend.components.meal-order.delete')
@endsection