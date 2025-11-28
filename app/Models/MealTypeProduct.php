<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MealTypeProduct extends Model
{
    protected $table = 'meal_type_product'; 
    protected $fillable = ['product_id', 'meal_type_id'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function mealType()
    {
        return $this->belongsTo(MealType::class);
    }
}

