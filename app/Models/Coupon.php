<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = ['client_id', 'coupon_name', 'coupon_discount', 'expire_date', 'status'];

    protected $casts = [
        'expire_date' => 'datetime:Y-m-d',
        'status' => 'boolean',
        'coupon_discount' => 'float' 
    ];

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    // Validation in model boot
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($coupon) {
            // Ensure expire_date is in the future
            if ($coupon->expire_date->isPast()) {
                throw new \Illuminate\Validation\ValidationException(
                    'Expiration date must be in the future'
                );
            }
        });
    }

    // Helper method to check validity
    public function isValid(): bool
    {
        return $this->status && $this->expire_date->isFuture();
    }

    // Scope for active coupons
    public function scopeActive($query)
    {
        return $query->where('status', true)
            ->whereDate('expire_date', '>', now());
    }
}
