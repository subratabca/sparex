@extends('backend.layout.master')

@section('title', 'Admin || Profile')

@section('breadcum')
    <span class="text-muted fw-light">Admin /</span> Update Profile
@endsection

@section('content')
    @include('backend.components.profile.common')
    @include('backend.components.profile.profile')
    <script>
        (async () => {
            await getProfile();
        })();
    </script>
@endsection
