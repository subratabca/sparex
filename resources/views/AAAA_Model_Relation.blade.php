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
    public function assignedDeliveryLedgers()
    {
        return $this->hasMany(DeliveryChargeLedger::class, 'delivery_person_id');
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

    public function mealOrders()
    {
        return $this->hasMany(MealOrder::class, 'customer_id');
    }

    public function clientMealOrders()
    {
        return $this->hasMany(ClientMealOrder::class, 'client_id');
    }

    public function deliveryChargeLedgers()
    {
        return $this->hasMany(DeliveryChargeLedger::class, 'client_id');
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

    public function mealOrderItems()
    {
        return $this->hasMany(MealOrderItem::class, 'product_id');
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

    public function deliveryChargeLedgers()
    {
        return $this->hasMany(DeliveryChargeLedger::class, 'meal_type_id');
    }

    public function mealOrderItems()
    {
        return $this->hasMany(MealOrderItem::class, 'meal_type_id');
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

    public function deliveryChargeLedgers()
    {
        return $this->hasMany(DeliveryChargeLedger::class, 'meal_order_id');
    }
}


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

    public function deliveryChargeLedgers()
    {
        return $this->hasMany(DeliveryChargeLedger::class, 'meal_order_id', 'meal_order_id')
                    ->where('client_id', $this->client_id);
    }
}


<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryChargeLedger extends Model
{
    protected $fillable = ['meal_order_id','customer_id','client_id','delivery_person_id','meal_type_id','delivery_date','order_tracking','delivery_status','delivery_charge','distance_km','distance_category','payment_status','payment_date','payment_notes','is_charge_counted','charge_key'
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

    // Relationships
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

    public function mealOrderItems()
    {
        return $this->hasMany(MealOrderItem::class, 'delivery_charge_ledger_id');
    }

    public function statusHistories()
    {
        return $this->hasMany(MealDeliveryStatusHistory::class, 'delivery_charge_ledger_id');
    }

    // Generate unique charge key
    public static function generateChargeKey($mealOrderId, $clientId, $mealTypeId, $deliveryDate)
    {
        return "MO{$mealOrderId}_C{$clientId}_MT{$mealTypeId}_" . str_replace('-', '', $deliveryDate);
    }

    // Generate unique tracking number
    public static function generateTrackingNumber()
    {
        return 'DL' . strtoupper(\Illuminate\Support\Str::random(8)) . date('Ymd');
    }

    // Status label helper
    public function getStatusLabelAttribute()
    {
        return self::STATUS_LABELS[$this->delivery_status] ?? 'Unknown';
    }

    // Scope for active deliveries
    public function scopeActive($query)
    {
        return $query->whereNotIn('delivery_status', [self::STATUS_DELIVERED, self::STATUS_CANCELLED]);
    }

    // Scope for pending deliveries
    public function scopePending($query)
    {
        return $query->where('delivery_status', self::STATUS_PENDING);
    }

    // Scope for today's deliveries
    public function scopeForToday($query)
    {
        return $query->whereDate('delivery_date', today());
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MealDeliveryStatusHistory extends Model
{
    protected $fillable = ['delivery_charge_ledger_id','delivery_status','picked_up_at','delivered_at','notes','updated_by_id','updated_by_type'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'picked_up_at' => 'datetime',
        'delivered_at' => 'datetime'
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


Remember above model relation.Nothing to do anything now or explain.



There is 4 type user in User model with role admin,client,customer,delivery. In this project customer can order multiple meal from multiple clients(client means who will provide meal) for future booking.Meal type include breakfast,lunch,snacks,dinner. each meal type may have multiple items like breakfast have 3 items from 3 different clients. For meal delivery to customer there is a delivery charge which will calculate on distance wise from client location to customer meal shipping address.suppose customer-A order for breakfast for 3 items.1 item will come from client-1 and 2 items will come from client-2 then it will count 2 delivery charge one for clent-1 and 2nd for client-2 for same meal type for same day.if same customer may have order for dinner then delivery charge will be calculate same as breakfast this way.