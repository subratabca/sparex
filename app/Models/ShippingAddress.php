<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingAddress extends Model
{
    protected $fillable = ['order_id', 'name', 'email', 'phone', 'address1', 'address2', 'zip_code', 'country_id', 'county_id', 'city_id'];

    public function order()
    {
        return $this->belongsTo(Order::class);
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
