<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserHealthProfile extends Model
{
    protected $fillable = [
        'user_id', 'gender', 'age', 'weight', 'height', 'description', 'period',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}