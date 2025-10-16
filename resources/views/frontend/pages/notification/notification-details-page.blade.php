@extends('frontend.layout.app')
@section('title', 'User || Notification Details')
@section('content')
    @include('frontend.components.notification.notification-details')
    <script>
        (async () => {
            await NotificationsByType();
        })()
    </script>
@endsection