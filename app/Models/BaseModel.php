<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class BaseModel extends Authenticatable
{
    public function setAttribute($key, $value)
    {
        // Do NOT modify sensitive fields
        if (
            is_string($value) &&
            !in_array($key, ['password', 'remember_token'])
        ) {
            $value = strtolower($value);
        }

        return parent::setAttribute($key, $value);
    }


    // public function setAttribute111($key, $value)
    // {
    //     // Convert to lowercase if it's a string value
    //     if (is_string($value)) {
    //         $value = strtolower($value);
    //     }
        
    //     return parent::setAttribute($key, $value);
    // }
}






