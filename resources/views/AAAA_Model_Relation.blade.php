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

class MealCart extends Model
{
    protected $fillable = ['meal_date','client_id','customer_id','meal_type_id','product_id','quantity','unit_price','total_price'];

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
    protected $fillable = ['meal_order_id','meal_date','client_id','meal_type_id','product_id','quantity','unit_price','total_price','status'];

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
    protected $fillable = ['meal_order_id','client_id','subtotal','tax','delivery_fee','payable_amount','paid_amount','payment_status','status'];

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

class MealDeliveryCharge extends Model
{
    protected $fillable = [
        'client_id',
        'meal_type_id',
        'inside_city_2km',
        'inside_city_5km',
        'inside_city_10km',
        'inside_city_above_10km',
    ];

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

class CreditTransaction extends Model
{
    protected $fillable = ['customer_id','type','method','amount','balance_after','transaction_id','currency','description'];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('credit_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');

            // Transaction Type: credit = add money, debit = spend money
            $table->enum('type', ['credit', 'debit']);

            // Payment method for credit additions
            $table->enum('method', ['cash', 'stripe', 'paypal'])->nullable();

            // Amount credited or debited
            $table->decimal('amount', 10, 2);

            // Balance after this transaction (very useful)
            $table->decimal('balance_after', 10, 2)->default(0);

            // Optional: transaction reference (Stripe ID, PayPal ID, Invoice No.)
            $table->string('transaction_id', 100)->nullable(); 
            $table->string('currency', 10)->nullable();

            // Optional: any notes (e.g. "Used for breakfast meal", "Admin adjustment")
            $table->text('description')->nullable();

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_transactions');
    }
};




Remember above model relationship.Nothing to do anything.