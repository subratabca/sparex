<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MealOrder extends Model
{
    protected $fillable = ['customer_id', 'order_date', 'status'];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function items()
    {
        return $this->hasMany(MealOrderItem::class, 'meal_order_id');
    }
}
