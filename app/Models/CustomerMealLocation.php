<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerMealLocation extends Model
{
    protected $fillable = [
        'customer_id', 'label', 'name', 'phone',
        'address1', 'address2', 'zip_code',
        'country_id', 'county_id', 'city_id',
        'latitude', 'longitude', 'is_default',
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function county()
    {
        return $this->belongsTo(County::class, 'county_id');
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id');
    }
}
