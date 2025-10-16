<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductShare extends Model
{
   protected $fillable = ['customer_id', 'product_id', 'recipient_email', 'shared_via'];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
