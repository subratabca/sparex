<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerMenu extends Model
{
    protected $table = 'customer_menus';

    protected $fillable = ['customer_id','meal_type_id','name','description'];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function mealType()
    {
        return $this->belongsTo(MealType::class, 'meal_type_id');
    }

}

