<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacebookUser extends Model
{
    protected $fillable = ['user_id', 'provider', 'provider_id', 'access_token'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
