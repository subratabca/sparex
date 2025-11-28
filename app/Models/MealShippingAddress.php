<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MealShippingAddress extends Model
{
    protected $fillable = ['meal_order_id','name','email','phone','address1','address2','zip_code','country_id','county_id','city_id','latitude','longitude'];

    public function mealOrder()
    {
        return $this->belongsTo(MealOrder::class, 'meal_order_id');
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
