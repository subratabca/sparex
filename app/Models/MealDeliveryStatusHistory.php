<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MealDeliveryStatusHistory extends Model
{
    protected $fillable = ['delivery_charge_ledger_id','delivery_status','pick_up_at','notes','updated_by_id','updated_by_type'
    ];

    protected $casts = [
        'pick_up_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Delivery Status Constants
    const STATUS_PENDING = 'pending';
    const STATUS_ACCEPT_ORDER = 'accept_order';
    const STATUS_ACCEPT_DELIVERY = 'accept_delivery';
    const STATUS_PREPARING = 'preparing';
    const STATUS_READY_FOR_PICKUP = 'ready_for_pickup';
    const STATUS_PICKED_UP = 'picked_up';
    const STATUS_ON_THE_WAY = 'on_the_way';
    const STATUS_ARRIVED = 'arrived';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_CANCELLED = 'cancelled';

    // Updated By Types
    const UPDATED_BY_CLIENT = 'client';
    const UPDATED_BY_DELIVERY_PERSON = 'delivery_person';
    const UPDATED_BY_SYSTEM = 'system';
    const UPDATED_BY_CUSTOMER = 'customer';
    const UPDATED_BY_ADMIN = 'admin';

    // Status labels
    const STATUS_LABELS = [
        self::STATUS_PENDING => 'Pending',
        self::STATUS_ACCEPT_ORDER => 'Accept Order',
        self::STATUS_PREPARING => 'Preparing',
        self::STATUS_READY_FOR_PICKUP => 'Ready for Pickup',
        self::STATUS_PICKED_UP => 'Picked Up',
        self::STATUS_ON_THE_WAY => 'On the Way',
        self::STATUS_ARRIVED => 'Arrived',
        self::STATUS_DELIVERED => 'Delivered',
        self::STATUS_CANCELLED => 'Cancelled',
    ];

    // Relationships
    public function deliveryChargeLedger()
    {
        return $this->belongsTo(DeliveryChargeLedger::class, 'delivery_charge_ledger_id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by_id');
    }

    // Scope for filtering by updater type
    public function scopeByUpdaterType($query, $type)
    {
        return $query->where('updated_by_type', $type);
    }

    // Scope for filtering by delivery status
    public function scopeByStatus($query, $status)
    {
        return $query->where('delivery_status', $status);
    }

    public function getUpdatedByLabelAttribute()
    {
        $labels = [
            self::UPDATED_BY_CLIENT => 'Restaurant',
            self::UPDATED_BY_DELIVERY_PERSON => 'Delivery Person',
            self::UPDATED_BY_SYSTEM => 'System',
            self::UPDATED_BY_CUSTOMER => 'Customer',
            self::UPDATED_BY_ADMIN => 'Admin',
        ];

        return $labels[$this->updated_by_type] ?? ucfirst(str_replace('_', ' ', $this->updated_by_type));
    }

    // Status label helper
    public function getStatusLabelAttribute()
    {
        return self::STATUS_LABELS[$this->delivery_status] ?? 'Unknown';
    }
}