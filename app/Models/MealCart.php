<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MealCart extends Model
{
    protected $fillable = ['meal_date','client_id','customer_id','meal_type_id','product_id','quantity','unit_price','total_price'];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
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
