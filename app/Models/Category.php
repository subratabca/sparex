<?php

namespace App\Models;

//use Illuminate\Database\Eloquent\Model;

class Category extends BaseModel
{
    protected $fillable = ['name','max_request_by_customer','image'];
}
