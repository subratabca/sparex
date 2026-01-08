<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MealOrderItem extends Model
{
    protected $fillable = ['meal_order_id','meal_date','meal_time','client_id','meal_type_id','product_id','quantity','unit_price','total_price'];

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

    // Delivery details are now handled through DeliveryChargeLedger
    public function deliveryChargeLedger()
    {
        return $this->belongsTo(DeliveryChargeLedger::class, 'delivery_charge_ledger_id');
    }

    // Helper method to get delivery status through DeliveryChargeLedger
    public function getDeliveryStatusAttribute()
    {
        return $this->deliveryChargeLedger->delivery_status ?? null;
    }

    // Helper method to get delivery person through DeliveryChargeLedger
    public function getDeliveryPersonAttribute()
    {
        return $this->deliveryChargeLedger->deliveryPerson ?? null;
    }

    // Helper method to get tracking number through DeliveryChargeLedger
    public function getTrackingNumberAttribute()
    {
        return $this->deliveryChargeLedger->order_tracking ?? null;
    }
}