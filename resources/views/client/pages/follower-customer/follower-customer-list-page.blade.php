@extends('client.layout.master')

@section('title', 'Client || Follower Customer')

@section('breadcum')
    <span class="text-muted fw-light">Client /</span> Follower Customer List
@endsection

@section('content')
    @include('client.components.follower-customer.follower-customer-list')
    @include('client.components.follower-customer.delete')
@endsection