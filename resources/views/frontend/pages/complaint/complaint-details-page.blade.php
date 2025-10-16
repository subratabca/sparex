@extends('frontend.layout.app')
@section('title', 'User || Complaint Details')
@section('content')
    @include('frontend.components.complaint.complaint-details')
    @include('frontend.components.complaint.reply-modaL')
@endsection