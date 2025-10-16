<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Follower extends Model
{
    protected $fillable = ['client_id','customer_id','status'];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id'); 
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id'); 
    }
}
