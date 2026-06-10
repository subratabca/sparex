<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientMealOrder extends Model
{
    protected $fillable = ['meal_order_id','client_id','subtotal','tax','platform_fee','delivery_fee','payable_amount','payment_status'];

    public function mealOrder()
    {
        return $this->belongsTo(MealOrder::class);
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function mealOrderItems()
    {
        return $this->hasMany(MealOrderItem::class, 'meal_order_id', 'meal_order_id')
                    ->where('client_id', $this->client_id);
    }

    public function deliveryChargeLedgers()
    {
        return $this->hasMany(DeliveryChargeLedger::class, 'meal_order_id', 'meal_order_id')
                    ->where('client_id', $this->client_id);
    }

    public function paymentHistories()
    {
        return $this->hasMany(ClientMealPaymentHistory::class, 'client_meal_order_id');
    }
}


