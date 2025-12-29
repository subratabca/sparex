<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MealDelivery extends Model
{
    protected $fillable = [
        'meal_order_item_id',
        'delivery_person_id',
        'delivery_status',
        'estimated_delivery_time',
        'actual_delivery_time',
        'pickup_time',
        'handover_time',
        'delivery_notes',
        'tracking_code',
        'current_location_lat',
        'current_location_lng',
        'proof_of_delivery_image'
    ];

    protected $casts = [
        'estimated_delivery_time' => 'datetime',
        'actual_delivery_time' => 'datetime',
        'pickup_time' => 'datetime',
        'handover_time' => 'datetime'
    ];

    // Delivery Status Constants
    const STATUS_PENDING = 'pending';
    const STATUS_PREPARING = 'preparing';
    const STATUS_READY_FOR_PICKUP = 'ready_for_pickup';
    const STATUS_PICKED_UP = 'picked_up';
    const STATUS_ON_THE_WAY = 'on_the_way';
    const STATUS_ARRIVED = 'arrived';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';

    // Relationships
    public function mealOrderItem()
    {
        return $this->belongsTo(MealOrderItem::class);
    }

    public function deliveryPerson()
    {
        return $this->belongsTo(User::class, 'delivery_person_id');
    }

    public function statusHistories()
    {
        return $this->hasMany(MealDeliveryStatusHistory::class);
    }

    // Helper method to get customer through relationships
    public function customer()
    {
        return $this->mealOrderItem->mealOrder->customer ?? null;
    }

    // Helper method to get client through relationships
    public function client()
    {
        return $this->mealOrderItem->client ?? null;
    }

    // Helper method to get product
    public function product()
    {
        return $this->mealOrderItem->product ?? null;
    }

    // Helper method to get meal type
    public function mealType()
    {
        return $this->mealOrderItem->mealType ?? null;
    }

    public function getStatusLabelAttribute()
    {
        $statusLabels = [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_PREPARING => 'Preparing',
            self::STATUS_READY_FOR_PICKUP => 'Ready for Pickup',
            self::STATUS_PICKED_UP => 'Picked Up',
            self::STATUS_ON_THE_WAY => 'On the Way',
            self::STATUS_ARRIVED => 'Arrived at Location',
            self::STATUS_DELIVERED => 'Delivered',
            self::STATUS_FAILED => 'Delivery Failed',
            self::STATUS_CANCELLED => 'Cancelled',
        ];

        return $statusLabels[$this->delivery_status] ?? 'Unknown';
    }

    public function isDelivered()
    {
        return $this->delivery_status === self::STATUS_DELIVERED;
    }

    public function isInTransit()
    {
        return in_array($this->delivery_status, [
            self::STATUS_PICKED_UP,
            self::STATUS_ON_THE_WAY,
            self::STATUS_ARRIVED
        ]);
    }
}