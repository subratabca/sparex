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


