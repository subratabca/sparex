@extends('delivery.layout.master')

@section('title', 'Delivery || Profile')

@section('breadcum')
    <span class="text-muted fw-light">Delivery /</span> Update Profile
@endsection

@section('content')
    @include('delivery.components.profile.common')
    @include('delivery.components.profile.profile')

    <script>
        (async () => {
            await getProfile();

        })();
    </script>

@endsection
