@extends('frontend.layout.app')
@section('title', 'User || Meal Types')
@section('content')
    @include('frontend.components.meal-type.index')
    @include('frontend.components.meal-type.delete')
@endsection