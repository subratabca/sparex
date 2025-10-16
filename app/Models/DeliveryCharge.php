<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryCharge extends Model
{
    protected $fillable = [
        'client_id',
        'inside_city_2kg',
        'inside_city_5kg',
        'inside_city_10kg',
        'inside_city_above_10kg',
        'outside_city_2kg',
        'outside_city_5kg',
        'outside_city_10kg',
        'outside_city_above_10kg',
    ];

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }
}

