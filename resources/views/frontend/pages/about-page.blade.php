@extends('frontend.layout.app')
@section('title', 'About')
@section('content')
    @include('frontend.components.about')
    <script>
        (async () => {
            await AboutInfo();
        })()
    </script>
@endsection