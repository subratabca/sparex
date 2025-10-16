@extends('backend.layout.auth.app')
@section('title', 'Admin || Reset Password Page')
@section('banner', 'Reset Password Page')
@section('content')
    @include('backend.components.auth.reset-password-form')
@endsection