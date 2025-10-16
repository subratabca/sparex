@extends('frontend.layout.app')

@section('title', $product->name ?? 'Item Details')
@section('og_description', strip_tags($product->description ?? 'No description available'))
@section('og_title', $product->name ?? 'Item Details')
@section('og_description', $product->description ?? 'No description available')
@section('og_image', url('/upload/product/large/' . ($product->image ?? 'no_image.jpg')))
@section('og_image_secure', url('/upload/product/large/' . ($product->image ?? 'no_image.jpg')))
@section('og_url', url('/product-details/' . ($product->id ?? '')))
@section('og_type', 'Online spare items website')
@section('content')
    @include('frontend.components.product-details')
    @include('frontend.components.customer-email-modal')
    <script>
        (async () => {
            await getProductDetails();
        })()
    </script>
@endsection




