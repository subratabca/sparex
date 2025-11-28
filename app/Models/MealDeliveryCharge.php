<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MealDeliveryCharge extends Model
{
    protected $fillable = ['client_id','meal_type_id','inside_city_2km','inside_city_5km','inside_city_10km','inside_city_above_10km'];

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function mealType()
    {
        return $this->belongsTo(MealType::class, 'meal_type_id');
    }
}
