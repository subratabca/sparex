<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientMealPaymentHistory extends Model
{
    protected $fillable = [
        'client_meal_order_id',
        'type',
        'payment_method',
        'paid_amount',
        'transaction_id',
        'currency',
        'payment_notes',
    ];

    protected $casts = [
        'paid_amount' => 'decimal:2',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
    ];

    public function clientMealOrder()
    {
        return $this->belongsTo(ClientMealOrder::class, 'client_meal_order_id');
    }
}
