@extends('client.layout.auth.app')
@section('title', 'Client || Reset Password Page')
@section('banner', 'Reset Password Page')
@section('content')
    @include('client.components.auth.reset-password-form')
@endsection