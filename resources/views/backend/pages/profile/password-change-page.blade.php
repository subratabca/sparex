@extends('backend.layout.master')

@section('title', 'Admin || Update Password')

@section('breadcum')
    <span class="text-muted fw-light">Admin /</span> Update Password
@endsection

@section('content')
    @include('backend.components.profile.common')
    @include('backend.components.profile.password-change')

    <script>
        (async () => {
            await getProfile();

        })();
    </script>
@endsection