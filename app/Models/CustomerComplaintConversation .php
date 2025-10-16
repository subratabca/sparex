<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerComplaintConversation  extends Model
{
    protected $fillable = ['customer_complain_id','sender_id','reply_message','sender_role'];

    public function customerComplaint()
    {
        return $this->belongsTo(CustomerComplaint::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
