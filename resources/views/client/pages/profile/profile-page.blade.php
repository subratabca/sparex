@extends('client.layout.master')

@section('title', 'Client || Profile')

@section('breadcum')
    <span class="text-muted fw-light">Client /</span> Update Profile
@endsection

@section('content')
    @include('client.components.profile.common')
    @include('client.components.profile.profile')

    <script>
        (async () => {
            await getProfile();

        })();
    </script>

@endsection
