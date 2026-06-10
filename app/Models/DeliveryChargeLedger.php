<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryChargeLedger extends Model
{
    protected $fillable = ['meal_order_id','customer_id','client_id','delivery_person_id','meal_type_id','delivery_date','order_tracking','delivery_status','delivery_charge','rider_platform_fee','payable_amount','distance_km','distance_category','payment_status','is_charge_counted','charge_key'];

    protected $casts = [
        'delivery_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Delivery Status Constants
    const STATUS_PENDING = 'pending';
    const STATUS_ACCEPT_ORDER = 'accept_order';
    const STATUS_PREPARING = 'preparing';
    const STATUS_READY_FOR_PICKUP = 'ready_for_pickup';
    const STATUS_PICKED_UP = 'picked_up';
    const STATUS_ON_THE_WAY = 'on_the_way';
    const STATUS_ARRIVED = 'arrived';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_CANCELLED = 'cancelled';

    // Payment Status
    const PAYMENT_DUE = 'due';
    const PAYMENT_PAID = 'paid';
    const PAYMENT_CANCELLED = 'cancelled';

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

public const STATUS_PROGRESS = [
    'pending' => 10,
    'accept_order' => 20,
    'preparing' => 40,
    'ready_for_pickup' => 60,
    'out_for_delivery' => 80,
    'delivered' => 100,
    'cancelled' => 0,
];

    public function mealOrder()
    {
        return $this->belongsTo(MealOrder::class, 'meal_order_id');
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function deliveryPerson()
    {
        return $this->belongsTo(User::class, 'delivery_person_id');
    }

    public function mealType()
    {
        return $this->belongsTo(MealType::class, 'meal_type_id');
    }

    public function paymentHistories()
    {
        return $this->hasMany(MealDeliveryPaymentHistory::class, 'delivery_charge_ledger_id');
    }
    
    public function statusHistories()
    {
        return $this->hasMany(MealDeliveryStatusHistory::class, 'delivery_charge_ledger_id');
    }

    public static function generateChargeKey($mealOrderId, $clientId, $mealTypeId, $deliveryDate)
    {
        return "MO{$mealOrderId}_C{$clientId}_MT{$mealTypeId}_" . str_replace('-', '', $deliveryDate);
    }

    public static function generateTrackingNumber()
    {
        return 'DL' . strtoupper(\Illuminate\Support\Str::random(8)) . date('Ymd');
    }

}