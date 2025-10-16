@extends('backend.layout.auth.app')
@section('title', 'Admin || OTP Page')
@section('content')
    @include('backend.components.auth.send-otp-form')
@endsection