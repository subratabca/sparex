@extends('frontend.layout.app')
@section('title', 'Home')
@section('content')

@include('frontend.components.home.hero')
@include('frontend.components.home.product-list')
<script>
    (async () => {
        showLoader(); 
        try {
            await Promise.all([
                getHeroInfo(), 
                getProductList() 
            ]);
        } catch (error) {
            handleError(error);
        } finally {
            hideLoader(); 
        }
    })();
</script>

@endsection

