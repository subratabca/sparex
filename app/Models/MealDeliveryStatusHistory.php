<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MealDeliveryStatusHistory extends Model
{
    protected $fillable = [
        'meal_delivery_id',
        'delivery_status',
        'notes',
        'location_lat',
        'location_lng',
        'updated_by_id',
        'updated_by_type'
    ];

    protected $casts = [
        'created_at' => 'datetime'
    ];

    // Updated By Types
    const UPDATED_BY_CLIENT = 'client';
    const UPDATED_BY_DELIVERY_PERSON = 'delivery_person';
    const UPDATED_BY_SYSTEM = 'system';
    const UPDATED_BY_CUSTOMER = 'customer';
    const UPDATED_BY_ADMIN = 'admin';

    // Relationships
    public function mealDelivery()
    {
        return $this->belongsTo(MealDelivery::class);
    }

    public function updatedBy()
    {
        if ($this->updated_by_type === 'user') {
            return $this->belongsTo(User::class, 'updated_by_id');
        }
        
        // For other types (client, delivery_person, etc.)
        return $this->belongsTo(User::class, 'updated_by_id');
    }

    // Scope for filtering by updater type
    public function scopeByUpdaterType($query, $type)
    {
        return $query->where('updated_by_type', $type);
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
        $statusLabels = MealDelivery::STATUS_LABELS ?? [
            'pending' => 'Pending',
            'preparing' => 'Preparing',
            'ready_for_pickup' => 'Ready for Pickup',
            'picked_up' => 'Picked Up',
            'on_the_way' => 'On the Way',
            'arrived' => 'Arrived',
            'delivered' => 'Delivered',
            'failed' => 'Failed',
            'cancelled' => 'Cancelled',
        ];

        return $statusLabels[$this->delivery_status] ?? 'Unknown';
    }
}