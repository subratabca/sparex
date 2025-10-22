<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MealOrderItem extends Model
{
    protected $fillable = ['meal_order_id', 'meal_type_id', 'menu_id','quantity','unit_price','total_price','status'];

    public function mealOrder()
    {
        return $this->belongsTo(MealOrder::class, 'meal_order_id');
    }

    public function mealType()
    {
        return $this->belongsTo(MealType::class, 'meal_type_id');
    }

    public function menu()
    {
        return $this->belongsTo(CustomerMenu::class, 'menu_id');
    }
}
