<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DeliveryChargeLedger extends Model
{
    use HasFactory;

    protected $fillable = ['meal_order_id','customer_id','client_id','delivery_person_id','meal_type_id','delivery_date','delivery_charge','distance_km','distance_category','payment_status','payment_date','payment_notes','is_charge_counted','charge_key'];

    protected $casts = [
        'delivery_date' => 'date',
        'delivery_charge' => 'decimal:2',
        'distance_km' => 'decimal:2',
        'payment_date' => 'datetime',
        'is_charge_counted' => 'boolean',
    ];

    // Generate unique charge key
    public static function generateChargeKey($mealOrderId, $clientId, $mealTypeId, $deliveryDate)
    {
        return "order_{$mealOrderId}_client_{$clientId}_meal_{$mealTypeId}_date_{$deliveryDate}";
    }

    /**
     * RELATIONSHIPS
     */

    // Main meal order
    public function mealOrder()
    {
        return $this->belongsTo(MealOrder::class, 'meal_order_id');
    }

    // Customer who placed the order
    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    // Client (meal provider)
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    // Delivery person assigned
    public function deliveryPerson()
    {
        return $this->belongsTo(User::class, 'delivery_person_id');
    }

    // Meal type (breakfast, lunch, snacks, dinner)
    public function mealType()
    {
        return $this->belongsTo(MealType::class, 'meal_type_id');
    }

    // Meal order items associated with this delivery charge
    public function mealOrderItems()
    {
        return $this->hasMany(MealOrderItem::class, 'delivery_charge_ledger_id');
    }

    // Delivery records (meal deliveries)
    public function mealDeliveries()
    {
        return $this->hasManyThrough(
            MealDelivery::class,
            MealOrderItem::class,
            'delivery_charge_ledger_id', // Foreign key on MealOrderItem table
            'meal_order_item_id', // Foreign key on MealDelivery table
            'id', // Local key on DeliveryChargeLedger table
            'id' // Local key on MealOrderItem table
        );
    }

    // Client meal order (payment record for client)
    public function clientMealOrder()
    {
        return $this->hasOne(ClientMealOrder::class, 'meal_order_id', 'meal_order_id')
                    ->where('client_id', $this->client_id);
    }

    /**
     * SCOPES
     */

    // Scope for due payments
    public function scopeDue($query)
    {
        return $query->where('payment_status', 'due');
    }

    // Scope for paid payments
    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    // Scope for cancelled payments
    public function scopeCancelled($query)
    {
        return $query->where('payment_status', 'cancelled');
    }

    // Scope for specific delivery date
    public function scopeForDate($query, $date)
    {
        return $query->where('delivery_date', $date);
    }

    // Scope for specific client
    public function scopeForClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    // Scope for specific delivery person
    public function scopeForDeliveryPerson($query, $deliveryPersonId)
    {
        return $query->where('delivery_person_id', $deliveryPersonId);
    }

    // Scope for specific meal type
    public function scopeForMealType($query, $mealTypeId)
    {
        return $query->where('meal_type_id', $mealTypeId);
    }

    // Scope for unassigned deliveries (no delivery person)
    public function scopeUnassigned($query)
    {
        return $query->whereNull('delivery_person_id');
    }

    // Scope for assigned deliveries (has delivery person)
    public function scopeAssigned($query)
    {
        return $query->whereNotNull('delivery_person_id');
    }

    /**
     * HELPER METHODS
     */

    // Check if delivery charge already counted for this combination
    public static function isChargeCounted($mealOrderId, $clientId, $mealTypeId, $deliveryDate)
    {
        $chargeKey = self::generateChargeKey($mealOrderId, $clientId, $mealTypeId, $deliveryDate);
        
        return self::where('charge_key', $chargeKey)
            ->where('is_charge_counted', true)
            ->exists();
    }

    // Get all delivery charge ledgers for a specific meal order
    public static function forMealOrder($mealOrderId)
    {
        return self::where('meal_order_id', $mealOrderId)->get();
    }

    // Get delivery charges grouped by client for a specific meal order
    public static function groupByClientForOrder($mealOrderId)
    {
        return self::where('meal_order_id', $mealOrderId)
            ->get()
            ->groupBy('client_id');
    }

    // Calculate total earnings for a specific delivery person
    public static function totalEarningsForDeliveryPerson($deliveryPersonId, $paymentStatus = null)
    {
        $query = self::where('delivery_person_id', $deliveryPersonId);
        
        if ($paymentStatus) {
            $query->where('payment_status', $paymentStatus);
        }
        
        return $query->sum('delivery_charge');
    }

    // Calculate total due earnings for a delivery person
    public static function totalDueEarningsForDeliveryPerson($deliveryPersonId)
    {
        return self::where('delivery_person_id', $deliveryPersonId)
            ->where('payment_status', 'due')
            ->sum('delivery_charge');
    }

    // Calculate total paid earnings for a delivery person
    public static function totalPaidEarningsForDeliveryPerson($deliveryPersonId)
    {
        return self::where('delivery_person_id', $deliveryPersonId)
            ->where('payment_status', 'paid')
            ->sum('delivery_charge');
    }

    // Mark as paid
    public function markAsPaid($paymentDate = null, $notes = null)
    {
        $this->update([
            'payment_status' => 'paid',
            'payment_date' => $paymentDate ?? now(),
            'payment_notes' => $notes,
        ]);

        return $this;
    }

    // Mark as due (if payment needs to be reversed)
    public function markAsDue()
    {
        $this->update([
            'payment_status' => 'due',
            'payment_date' => null,
        ]);

        return $this;
    }

    // Mark as cancelled
    public function markAsCancelled($notes = null)
    {
        $this->update([
            'payment_status' => 'cancelled',
            'payment_notes' => $notes,
        ]);

        return $this;
    }

    // Assign delivery person
    public function assignDeliveryPerson($deliveryPersonId)
    {
        $this->update([
            'delivery_person_id' => $deliveryPersonId,
        ]);

        return $this;
    }

    // Unassign delivery person
    public function unassignDeliveryPerson()
    {
        $this->update([
            'delivery_person_id' => null,
        ]);

        return $this;
    }

    // Check if payment is due
    public function isDue()
    {
        return $this->payment_status === 'due';
    }

    // Check if payment is paid
    public function isPaid()
    {
        return $this->payment_status === 'paid';
    }

    // Check if payment is cancelled
    public function isCancelled()
    {
        return $this->payment_status === 'cancelled';
    }

    // Check if delivery person is assigned
    public function hasDeliveryPerson()
    {
        return !is_null($this->delivery_person_id);
    }

    // Get delivery status from associated meal deliveries
    public function getDeliveryStatusAttribute()
    {
        $deliveries = $this->mealDeliveries;
        
        if ($deliveries->isEmpty()) {
            return $this->hasDeliveryPerson() ? 'assigned' : 'unassigned';
        }
        
        // Check if all deliveries are delivered
        $allDelivered = $deliveries->every(function ($delivery) {
            return $delivery->delivery_status === MealDelivery::STATUS_DELIVERED;
        });
        
        if ($allDelivered) {
            return 'delivered';
        }
        
        // Check if any delivery failed
        $anyFailed = $deliveries->contains(function ($delivery) {
            return $delivery->delivery_status === MealDelivery::STATUS_FAILED;
        });
        
        if ($anyFailed) {
            return 'failed';
        }
        
        // Check if any delivery is cancelled
        $anyCancelled = $deliveries->contains(function ($delivery) {
            return $delivery->delivery_status === MealDelivery::STATUS_CANCELLED;
        });
        
        if ($anyCancelled) {
            return 'cancelled';
        }
        
        // Check if any delivery is in transit
        $anyInTransit = $deliveries->contains(function ($delivery) {
            return in_array($delivery->delivery_status, [
                MealDelivery::STATUS_PICKED_UP,
                MealDelivery::STATUS_ON_THE_WAY,
                MealDelivery::STATUS_ARRIVED,
            ]);
        });
        
        if ($anyInTransit) {
            return 'in_transit';
        }
        
        // Check if any delivery is ready for pickup
        $anyReady = $deliveries->contains(function ($delivery) {
            return $delivery->delivery_status === MealDelivery::STATUS_READY_FOR_PICKUP;
        });
        
        if ($anyReady) {
            return 'ready_for_pickup';
        }
        
        return 'pending';
    }

    // Get readable payment status
    public function getPaymentStatusTextAttribute()
    {
        $statuses = [
            'due' => 'Due',
            'paid' => 'Paid',
            'cancelled' => 'Cancelled',
        ];
        
        return $statuses[$this->payment_status] ?? 'Unknown';
    }

    // Get distance category label
    public function getDistanceCategoryLabelAttribute()
    {
        $categories = [
            'inside_city_2km' => 'Within 2km',
            'inside_city_5km' => '2-5km',
            'inside_city_10km' => '5-10km',
            'inside_city_above_10km' => 'Above 10km',
        ];
        
        return $categories[$this->distance_category] ?? $this->distance_category;
    }

    // Get delivery status badge color
    public function getDeliveryStatusBadgeAttribute()
    {
        $status = $this->delivery_status;
        
        $badges = [
            'unassigned' => 'secondary',
            'assigned' => 'info',
            'pending' => 'warning',
            'ready_for_pickup' => 'primary',
            'in_transit' => 'info',
            'delivered' => 'success',
            'failed' => 'danger',
            'cancelled' => 'dark',
        ];
        
        return $badges[$status] ?? 'secondary';
    }

    // Get payment status badge color
    public function getPaymentStatusBadgeAttribute()
    {
        $badges = [
            'due' => 'warning',
            'paid' => 'success',
            'cancelled' => 'danger',
        ];
        
        return $badges[$this->payment_status] ?? 'secondary';
    }

}
