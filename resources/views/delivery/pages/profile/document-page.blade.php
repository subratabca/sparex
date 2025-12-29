@extends('delivery.layout.master')

@section('title', 'Delivery || Document Verify')

@section('breadcum')
    <span class="text-muted fw-light">Delivery /</span>Document Verification
@endsection

@section('content')
    @include('delivery.components.profile.document')
@endsection