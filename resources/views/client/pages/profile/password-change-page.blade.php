@extends('client.layout.master')

@section('title', 'Client || Update Password')

@section('breadcum')
    <span class="text-muted fw-light">Client /</span> Update Password
@endsection

@section('content')
    @include('client.components.profile.common')
    @include('client.components.profile.password-change')

    <script>
        (async () => {
            await getProfile();

        })();
    </script>
@endsection