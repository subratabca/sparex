@extends('frontend.layout.app')
@section('title', 'User || Customer Complain List')
@section('content')
    @include('frontend.components.customer-complain.customer-complain-list')
    @include('frontend.components.customer-complain.reply')
@endsection