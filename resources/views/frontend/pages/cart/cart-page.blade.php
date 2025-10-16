@extends('frontend.layout.app')
@section('title', 'User || My Cart')
@section('content')
    @include('frontend.components.cart.cart-list')
    @include('frontend.components.cart.delete')
@endsection