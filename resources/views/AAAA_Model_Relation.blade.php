<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends BaseUserModel
{
    use HasFactory, Notifiable;

    protected $fillable = ['firstName','lastName','email','mobile','image','role','password','accept_registration_tnc','otp','status','is_email_verified','address1','address2','zip_code','country_id','county_id','city_id','doc_image1','doc_image2','latitude','longitude'];
    
    protected $attributes = ['otp' => '0'];
    protected $hidden = ['password', 'remember_token', 'otp'];

    public function isClient()
    {
        return $this->role === 'client';
    }

    public function isCustomer()
    {
        return $this->role === 'customer';
    }

    public function isDelivery()
    {
        return $this->role === 'delivery';
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'client_id');
    }

    public function carts()
    {
        return $this->hasMany(Cart::class, 'customer_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'customer_id');
    }

    public function clientOrders()
    {
        return $this->hasMany(ClientOrder::class, 'client_id');
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class, 'client_id');
    }

    public function productComplaints() {
        return $this->hasMany(Complaint::class, 'customer_id');
    }

    //Complaint received by customer and complaint given by client
    public function receivedComplaints() { 
        return $this->hasMany(CustomerComplaint::class, 'customer_id');
    }

    public function bannedCustomers()
    {
        return $this->hasMany(BannedCustomer::class, 'customer_id');
    }

    public function bannedByClients()
    {
        return $this->hasMany(BannedCustomer::class, 'client_id');
    }

    public function followers()
    {
        return $this->hasMany(Follower::class, 'client_id');
    }

    public function productShares()
    {
        return $this->hasMany(ProductShare::class, 'customer_id');
    }

    public function scopeWithLocation($query)
    {
        return $query->with('country', 'county', 'city');
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function county()
    {
        return $this->belongsTo(County::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function deliveryCharge()
    {
        return $this->hasOne(DeliveryCharge::class, 'client_id');
    }

    public function mealCarts()
    {
        return $this->hasMany(MealCart::class, 'customer_id');
    }

    public function providedMealCarts()
    {
        return $this->hasMany(MealCart::class, 'client_id');
    }

    public function mealDeliveryCharges()
    {
        return $this->hasMany(MealDeliveryCharge::class, 'client_id');
    }

    public function creditTransactions()
    {
        return $this->hasMany(CreditTransaction::class, 'customer_id');
    }

    // Delivery Person Relationships
    public function assignedMealDeliveries()
    {
        return $this->hasMany(MealDelivery::class, 'delivery_person_id');
    }

    public function deliveryStatusUpdates()
    {
        return $this->hasMany(MealDeliveryStatusHistory::class, 'updated_by_id')
                    ->where('updated_by_type', 'delivery_person');
    }

    public function deliveryVehicle()
    {
        return $this->hasOne(DeliveryVehicle::class, 'delivery_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends BaseModel
{
  protected $fillable = ['client_id', 'category_id', 'brand_id', 'image', 'name','weight','price','discount_price','current_stock','address1','address2','country_id','county_id','city_id','zip_code','description','expire_date','collection_date', 'start_collection_time','end_collection_time','latitude','longitude','accept_tnc','status','has_availability','has_variants','has_brand', 'has_discount_price','is_free'];

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function productImages()
    {
        return $this->hasMany(ProductImage::class, 'product_id');
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class, 'product_id');
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    public function productShares()
    {
        return $this->hasMany(ProductShare::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function county()
    {
        return $this->belongsTo(County::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }
    
    public function mealTypes()
    {
        return $this->belongsToMany(MealType::class, 'meal_type_product', 'product_id', 'meal_type_id');
    }

    public function nutrient()
    {
        return $this->hasOne(ProductNutrient::class);
    }

    public function mealCarts()
    {
        return $this->hasMany(MealCart::class, 'product_id');
    }


}

<?php

namespace App\Models;

//use Illuminate\Database\Eloquent\Model;

class MealType extends BaseModel
{
    protected $fillable = ['name'];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'meal_type_product', 'meal_type_id', 'product_id');
    }

    public function mealKeywords()
    {
        return $this->hasMany(MealKeyword::class);
    }

    public function mealCarts()
    {
        return $this->hasMany(MealCart::class, 'meal_type_id');
    }

    public function deliveryCharges()
    {
        return $this->hasMany(MealDeliveryCharge::class, 'meal_type_id');
    }

}

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

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MealOrderItem extends Model
{
    protected $fillable = ['meal_order_id','meal_date','meal_time','client_id','meal_type_id','product_id','quantity','unit_price','total_price','delivery_status','delivery_person_id','handover_time','delivered_time'];

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

    public function deliveryPerson()
    {
        return $this->belongsTo(User::class, 'delivery_person_id');
    }

    public function deliveryStatusHistory()
    {
        return $this->hasMany(MealDeliveryStatusHistory::class, 'meal_order_item_id');
    }
}


<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientMealOrder extends Model
{
    protected $fillable = ['meal_order_id','client_id','subtotal','tax','delivery_fee','payable_amount','paid_amount','payment_status'];

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
        return $this->hasMany(MealOrderItem::class, 'client_id', 'client_id')
                    ->where('order_id', $this->order_id);
    }
}


<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MealDeliveryCharge extends Model
{
    protected $fillable = ['client_id','meal_type_id','inside_city_2km','inside_city_5km','inside_city_10km','inside_city_above_10km'];

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function mealType()
    {
        return $this->belongsTo(MealType::class, 'meal_type_id');
    }
}

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

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DeliveryChargeLedger extends Model
{
    use HasFactory;

    protected $fillable = ['meal_order_id','customer_id','client_id','delivery_person_id','meal_type_id','delivery_date','delivery_charge','distance_km','distance_category','payment_status','payment_date','payment_notes','is_charge_counted','charge_key',];

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

Remember above moel relation.Nothing to do now.



There is 4 type user in User model with role admin,client,customer,delivery. In this project customer can order multiple meal from multiple clients(client means who will provide meal) for future booking.Meal type include breakfast,lunch,snacks,dinner. each meal type may have multiple items like breakfast have 3 items from 3 different clients. For meal delivery to customer there is a delivery charge which will calculate on distance wise from client location to customer meal shipping address.suppose customer-A order for breakfast for 3 items.1 item will come from client-1 and 2 items will come from client-2 then it will count 2 delivery charge one for clent-1 and 2nd for client-2 for same meal type for same day.if same customer may have order for dinner then delivery charge will be calculate same as breakfast this way.