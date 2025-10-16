<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComplaintConversation extends Model
{
    protected $fillable = ['complaint_id','sender_id','reply_message','sender_role'];

    public function complaint()
    {
        return $this->belongsTo(Complaint::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
