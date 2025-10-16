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

