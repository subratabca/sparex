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
