@extends('frontend.layout.app')
@section('title', 'User || Email Share List')
@section('content')
    @include('frontend.components.email-share.email-share-list')
    @include('frontend.components.email-share.delete')
@endsection