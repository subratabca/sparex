@extends('frontend.layout.app')
@section('title', 'User || Customer Complain Details')
@section('content')
    @include('frontend.components.customer-complain.customer-complain-details')
    @include('frontend.components.customer-complain.reply')
@endsection