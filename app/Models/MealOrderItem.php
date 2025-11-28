<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MealOrderItem extends Model
{
    protected $fillable = ['meal_order_id','meal_date','client_id','meal_type_id','product_id','quantity','unit_price','total_price','status'];

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function mealOrder()
    {
        return $this->belongsTo(MealOrder::class, 'meal_order_id');
    }

    public function mealType()
    {
        return $this->belongsTo(MealType::class, 'meal_type_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
