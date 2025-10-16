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