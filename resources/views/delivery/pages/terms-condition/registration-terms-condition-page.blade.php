@extends('frontend.layout.app')
@section('title', 'Delivery Registration T&C')
@section('content')
    @include('frontend.components.terms-condition.delivery-registration-terms-condition')
    <script>
        (async () => {
            await DeliveryRegistrationTCInfo();
        })()
    </script>
@endsection