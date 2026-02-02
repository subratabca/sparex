<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MealDeliveryPaymentHistory extends Model
{
    protected $fillable = ['delivery_charge_ledger_id','type','payment_method','paid_amount','transaction_id','currency','payment_notes'];

    protected $casts = [
        'amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function deliveryChargeLedger()
    {
        return $this->belongsTo(DeliveryChargeLedger::class, 'delivery_charge_ledger_id');
    }
}
