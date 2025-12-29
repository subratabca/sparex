@extends('delivery.layout.master')

@section('title', 'Update Password')

@section('breadcum')
    <span class="text-muted fw-light">Delivery /</span> Update Password
@endsection

@section('content')
    @include('delivery.components.profile.common')
    @include('delivery.components.profile.password-change')

    <script>
        (async () => {
            await getProfile();

        })();
    </script>
@endsection