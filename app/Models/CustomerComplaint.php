<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerComplaint extends Model
{
    protected $fillable = ['client_id', 'customer_id', 'sender_role', 'status', 'message','cmp_date','cmp_time','customer_cmp_date','customer_cmp_time'];

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function customerComplaintConversations()
    {
        return $this->hasMany(CustomerComplaintConversion::class);
    }
}
