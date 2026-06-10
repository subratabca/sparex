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

use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    protected $fillable = ['product_id', 'image'];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}


<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductNutrient extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',

        // Macronutrients
        'calories', 'calories_unit',
        'protein', 'protein_unit',
        'fat', 'fat_unit',
        'carbohydrates', 'carbohydrates_unit',
        'fiber', 'fiber_unit',
        'sugar', 'sugar_unit',
        'cholesterol', 'cholesterol_unit',
        'sodium', 'sodium_unit',

        // Micronutrients
        'vitamin_a', 'vitamin_a_unit',
        'vitamin_c', 'vitamin_c_unit',
        'calcium', 'calcium_unit',
        'iron', 'iron_unit',
    ];

    /**
     * Each nutrient record belongs to a product.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
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

//use Illuminate\Database\Eloquent\Model;

class MealKeyword extends BaseModel
{
    protected $fillable = ['meal_type_id','name'];

    public function mealType()
    {
        return $this->belongsTo(MealType::class);
    }
}


<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MealTypeProduct extends Model
{
    protected $table = 'meal_type_product'; 
    protected $fillable = ['product_id', 'meal_type_id'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function mealType()
    {
        return $this->belongsTo(MealType::class);
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

class MealCart extends Model
{
    protected $fillable = ['meal_date','meal_time','client_id','customer_id','meal_type_id','product_id','quantity','unit_price','total_price'];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function mealType()
    {
        return $this->belongsTo(MealType::class, 'meal_type_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}


<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MealOrder extends Model
{
    protected $fillable = ['customer_id','status','service_fee','client_platform_fee','rider_platform_fee','delivery_type','delivery_fee','subtotal','tax','payable_amount','paid_amount','payment_type','payment_method','transaction_id','currency','order_number','invoice_no','payment_status'];

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

}


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

<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientMealPaymentHistory extends Model
{
    protected $fillable = [
        'client_meal_order_id',
        'type',
        'payment_method',
        'paid_amount',
        'transaction_id',
        'currency',
        'payment_notes',
    ];

    protected $casts = [
        'paid_amount' => 'decimal:2',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
    ];

    public function clientMealOrder()
    {
        return $this->belongsTo(ClientMealOrder::class, 'client_meal_order_id');
    }
}


<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MealShippingAddress extends Model
{
    protected $fillable = ['meal_order_id','name','email','phone','address1','address2','zip_code','country_id','county_id','city_id','latitude','longitude'];

    public function mealOrder()
    {
        return $this->belongsTo(MealOrder::class, 'meal_order_id');
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
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryChargeLedger extends Model
{
    protected $fillable = ['meal_order_id','customer_id','client_id','delivery_person_id','meal_type_id','delivery_date','order_tracking','delivery_status','delivery_charge','distance_km','distance_category','payment_status','is_charge_counted','charge_key'];

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


<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MealDeliveryPaymentHistory extends Model
{
    protected $fillable = ['delivery_charge_ledger_id','type','payment_method','paid_amount','transaction_id','currency','payment_notes'];

    protected $casts = [
        'amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function deliveryChargeLedger()
    {
        return $this->belongsTo(DeliveryChargeLedger::class, 'delivery_charge_ledger_id');
    }
}
