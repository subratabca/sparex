<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = ['user_id', 'role', 'activity_type', 'status', 'message', 'related_table', 'ip_address'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
