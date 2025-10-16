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
