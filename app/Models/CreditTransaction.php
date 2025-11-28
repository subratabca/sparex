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

