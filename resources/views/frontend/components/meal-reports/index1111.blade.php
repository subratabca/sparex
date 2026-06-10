@extends('frontend.components.dashboard.dashboard-master')

@section('dashboard-content')

    @include('frontend.components.meal-reports.filters')
    @include('frontend.components.meal-reports.kpi-cards')

    {{-- Order Chart + Table --}}
    <div class="row g-4 mt-1">
        <div class="col-lg-8">
            @include('frontend.components.meal-reports.order-chart')
        </div>
        <div class="col-lg-4">
            @include('frontend.components.meal-reports.payment-chart')
        </div>
    </div>

    {{-- Order Table --}}
    <div class="row g-4 mt-1">
        <div class="col-12">
            @include('frontend.components.meal-reports.order-table')
        </div>
    </div>

    {{-- Meal Type + Order Status --}}
    <div class="row g-4 mt-1">
        <div class="col-lg-8">
            @include('frontend.components.meal-reports.meal-type-chart')
        </div>
        <div class="col-lg-4">
            @include('frontend.components.meal-reports.order-status-chart')
        </div>
    </div>

    {{-- Meal Type Table --}}
    <div class="row g-4 mt-1">
        <div class="col-12">
            @include('frontend.components.meal-reports.meal-type-table')
        </div>
    </div>

    {{-- Spending Trend --}}
    <div class="row g-4 mt-1 mb-4">
        <div class="col-12">
            @include('frontend.components.meal-reports.spending-chart')
        </div>
    </div>

@endsection