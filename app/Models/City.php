<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $fillable = ['name', 'county_id'];
    
    public function county()
    {
        return $this->belongsTo(County::class);
    }
}
