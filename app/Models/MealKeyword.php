<?php

namespace App\Models;

//use Illuminate\Database\Eloquent\Model;

class MealKeyword extends BaseModel
{
    protected $fillable = ['name'];

    public function mealTypes()
    {
        return $this->belongsToMany(MealType::class, 'meal_keyword_meal_type');
    }
}
