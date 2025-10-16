<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = ['name','email','phone1','phone2','logo','website_name','slogan','address','city','country','zip_code','facebook','linkedin','youtube','description','refund','terms','privacy'];
}
