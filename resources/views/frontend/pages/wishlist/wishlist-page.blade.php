@extends('frontend.layout.app')
@section('title', 'User || Wishlist Page')
@section('content')
    @include('frontend.components.wishlist.wishlist-list')
    @include('frontend.components.wishlist.delete')
@endsection