<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
  protected $fillable = ['client_id', 'category_id', 'brand_id', 'image', 'name','weight','price','discount_price','current_stock','address1','address2','country_id','county_id','city_id','zip_code','description','expire_date','collection_date', 'start_collection_time','end_collection_time','latitude','longitude','accept_tnc','status','has_variants','has_brand', 'has_discount_price','is_free'];

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

}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $fillable = ['product_id', 'color', 'size', 'current_stock'];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class, 'variant_id');
    }
}


<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    
    const PAYMENT_TYPE_CASH = 'cash';
    const PAYMENT_METHOD_CASH = 'cash';
    const STATUS_PENDING = 'pending';

    protected $fillable = ['customer_id','status','delivery_type','subtotal','tax','delivery_fee','coupon_discount','payable_amount','paid_amount','payment_type','payment_method','transaction_id','currency','order_number','invoice_no','accept_order_request_tnc','accept_product_delivery_tnc'];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function clientOrders()
    {
        return $this->hasMany(ClientOrder::class);
    }

    public function shippingAddress()
    {
        return $this->hasOne(ShippingAddress::class);
    }

    public function complaints()
    {
        return $this->hasManyThrough(Complaint::class,OrderItem::class,   'order_id','order_item_id','id','id');
    }
}


<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = ['order_id','client_id','product_id','product_variant_id','quantity','unit_price','total_price','color','size','status','approve_date','approve_time','delivery_date','delivery_time','cancel_date','cancel_time'];

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function complaint()
    {
        return $this->hasOne(Complaint::class, 'order_item_id');
    }

    // Helpers
    public function isDelivered()
    {
        return $this->status === 'delivered';
    }

    public function isCanceled()
    {
        return $this->status === 'canceled';
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientOrder extends Model
{
    protected $fillable = ['order_id','client_id','subtotal','coupon_discount','tax','items_weight','delivery_fee','payable_amount','paid_amount','payment_status','status'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'client_id', 'client_id')
                    ->where('order_id', $this->order_id);
    }
}


<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientOrderAction extends Model
{
    protected $table = 'client_order_actions';

    protected $fillable = ['order_id','client_order_id','order_item_id','client_id','quantity','subtotal','coupon_discount','tax','delivery_fee','total_refund','payment_status','action_type','action_reason','action_by_id','action_by_role'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function clientOrder()
    {
        return $this->belongsTo(ClientOrder::class);
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function actionBy()
    {
        return $this->belongsTo(User::class, 'action_by_id');
    }
}





<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use HasFactory;

    const TYPE_UPLOAD = 'upload';
    const TYPE_SALE = 'sale';
    const TYPE_ADJUSTMENT = 'adjustment';
    const TYPE_CANCELED = 'cancel';
    const TYPE_RETURN = 'return';
    const TYPE_EXPIRED = 'expired';
    const TYPE_DAMAGED = 'damaged';

    protected $fillable = ['product_id','variant_id','client_id','order_id','quantity','movement_type','notes'];

    protected $casts = [
        'movement_type' => 'string',
        'quantity' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // Scopes
    public function scopeAdditions($query)
    {
        return $query->where('quantity', '>', 0);
    }

    public function scopeDeductions($query)
    {
        return $query->where('quantity', '<', 0);
    }

    public function scopeForProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function scopeForVariant($query, $variantId)
    {
        return $query->where('variant_id', $variantId);
    }

    // Helpers
    public function getFormattedQuantityAttribute()
    {
        return $this->quantity > 0 ? "+{$this->quantity}" : $this->quantity;
    }

    public function isAddition()
    {
        return $this->quantity > 0;
    }

    public function isDeduction()
    {
        return $this->quantity < 0;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    protected $fillable = ['order_item_id','customer_id','message','status','cmp_date','cmp_time','clnt_cmp_date','clnt_cmp_time','clnt_cmp_feedback_date','clnt_cmp_feedback_time'];


    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class, 'order_item_id');
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function conversations()
    {
        return $this->hasMany(ComplaintConversation::class);
    }
}



<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComplaintConversation extends Model
{
    protected $fillable = ['complaint_id','sender_id','reply_message','sender_role'];

    public function complaint()
    {
        return $this->belongsTo(Complaint::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = ['user_id', 'role', 'activity_type', 'status', 'message', 'related_table', 'ip_address'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

Note: status column of Order,ClientOrder model is $table->enum('status', ['pending','approved','delivered','canceled','partially_approved','partially_delivered'])->default('pending'); and status column of OrderItem model is $table->enum('status', ['pending','approved','delivered','canceled','returned'])->default('pending'); 
   Below is column of ClientOrderAction Model:
            $table->enum('action_type', ['canceled', 'returned', 'exchanged'])->nullable();
            $table->text('action_reason')->nullable();
            $table->unsignedBigInteger('action_by_id')->nullable();
            $table->enum('action_by_role', ['client', 'customer'])->nullable();

    status column of Complaint model is $table->enum('status',['pending','under_review','solved','further_investigation'])->default('pending');

Remember above model relation.Nothing to do.