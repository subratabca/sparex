<?php

namespace App\Models;

//use Illuminate\Database\Eloquent\Model;

class MealKeyword extends BaseModel
{
    protected $fillable = ['meal_type_id','name'];

    public function mealType()
    {
        return $this->belongsTo(MealType::class);
    }
}
