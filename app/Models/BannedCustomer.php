<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BannedCustomer extends Model
{
    protected $fillable = ['client_id','customer_id','message'];

    public function client()
    {
        return $this->belongsTo(User::class,'client_id');
    }

    public function customer()
    {
        return $this->belongsTo(User::class,'customer_id');
    }
}
