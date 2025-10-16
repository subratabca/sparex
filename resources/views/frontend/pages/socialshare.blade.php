@extends('frontend.layout.app')
@section('title', 'Share Food Details')
@section('meta_description', 'Check out this food item available for collection.')

{{-- Open Graph Meta Data for Food Sharing --}}
@section('og_title', isset($item) ? $item->name : 'Food Details')
@section('og_description', 'Find delicious food items available for collection.')
@section('og_image', isset($item) ? asset('upload/food/large/' . $item->image) : asset('upload/no_image.jpg'))
@section('og_image_secure', isset($item) ? asset('upload/food/large/' . $item->image) : asset('upload/no_image.jpg'))
@section('og_image_width', '1200')
@section('og_image_height', '630')
@section('og_url', isset($item) ? url('/food-details/' . $item->id) : url('/'))
@section('og_type', 'article')
@section('content')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"/>

        <div class="container mt-4">
            <h2 class="mt-5 text-center">Laravel Social Share</h2>
            <div class="social-btn-sp">

                {!! $shareButtons !!}

            </div>

            <table class="table">
                <tr>
                    <th>List Of Food Items</th>
                </tr>
                @foreach($items as $food)
                <tr>
                    <td>
                        <strong>{{ $food->name }}</strong><br>
                        <img src="{{ asset('upload/food/large/' . $food->image) }}" alt="{{ $food->name }}" width="100">
                        <br><br>

                        {!! Share::page(url('/food-details/' . $food->id))
                            ->facebook()->twitter()->whatsapp()->linkedin()
                        !!}
                    </td>
                </tr>
                @endforeach
            </table>
        </div>
@endsection


<style>
    .social-btn-sp #social-links {
        margin: 0 auto;
        max-width: 500px;
    }

    .social-btn-sp #social-links ul li {
        display: inline-block;
    }          

    .social-btn-sp #social-links ul li a {
        padding: 15px;
        border: 1px solid #ccc;
        margin: 1px;
        font-size: 30px;
    }

    table #social-links{
        display: inline-table;
    }

    table #social-links ul li{
        display: inline;
    }

    table #social-links ul li a{
        padding: 5px;
        border: 1px solid #ccc;
        margin: 1px;
        font-size: 15px;
        background: #e3e3ea;
    }

</style>