@extends('client.layout.auth.app')
@section('title', 'Client || OTP Page')
@section('content')
    @include('client.components.auth.send-otp-form')
@endsection