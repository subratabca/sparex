<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
  protected $fillable = ['client_id', 'category_id', 'brand_id', 'image', 'name','weight','price','discount_price','current_stock','address1','address2','country_id','county_id','city_id','zip_code','description','expire_date','collection_date', 'start_collection_time','end_collection_time','latitude','longitude','accept_tnc','status','has_variants','has_brand', 'has_discount_price','is_free'];

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function productImages()
    {
        return $this->hasMany(ProductImage::class, 'product_id');
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class, 'product_id');
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    public function productShares()
    {
        return $this->hasMany(ProductShare::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function county()
    {
        return $this->belongsTo(County::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

}

