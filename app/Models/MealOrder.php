<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MealOrder extends Model
{
    protected $fillable = ['customer_id','status','delivery_type','delivery_fee','subtotal','tax','payable_amount','paid_amount','payment_type','payment_method','transaction_id','currency','order_number','invoice_no','payment_status'];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function items()
    {
        return $this->hasMany(MealOrderItem::class, 'meal_order_id');
    }

    public function clientMealOrders()
    {
        return $this->hasMany(ClientMealOrder::class, 'meal_order_id');
    }

    public function mealShippingAddress()
    {
        return $this->hasOne(MealShippingAddress::class, 'meal_order_id');
    }
}
