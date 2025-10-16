<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    protected $fillable = ['order_item_id','customer_id','message','status','cmp_date','cmp_time','clnt_cmp_date','clnt_cmp_time','clnt_cmp_feedback_date','clnt_cmp_feedback_time'];


    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class, 'order_item_id');
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function conversations()
    {
        return $this->hasMany(ComplaintConversation::class);
    }


}
