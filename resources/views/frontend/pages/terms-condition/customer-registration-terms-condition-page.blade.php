@extends('frontend.layout.app')
@section('title', 'Customer Registration T&C')
@section('content')
    @include('frontend.components.terms-condition.customer-registration-terms-condition')
    <script>
        (async () => {
            await CustomerRegistrationTCInfo();
        })()
    </script>
@endsection