<?php

namespace App\Models;

//use Illuminate\Database\Eloquent\Model;

class MealType extends BaseModel
{
    protected $fillable = ['name'];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'meal_type_product', 'meal_type_id', 'product_id');
    }

    public function mealKeywords()
    {
        return $this->hasMany(MealKeyword::class);
    }

    public function mealCarts()
    {
        return $this->hasMany(MealCart::class, 'meal_type_id');
    }

    public function deliveryCharges()
    {
        return $this->hasMany(MealDeliveryCharge::class, 'meal_type_id');
    }

}
