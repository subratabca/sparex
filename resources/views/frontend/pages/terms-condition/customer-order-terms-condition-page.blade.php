@extends('frontend.layout.app')
@section('title', 'Customer Order T&C')
@section('content')
    @include('frontend.components.terms-condition.customer-order-terms-condition')
    <script>
        (async () => {
            await CustomerOrderTCInfo();
        })()
    </script>
@endsection