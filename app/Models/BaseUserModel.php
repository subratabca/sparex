<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class BaseUserModel extends Authenticatable
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
    
    /**
     * Convert all string attributes to lowercase before saving
     */
    // public function setAttribute($key, $value)
    // {
    //     if (is_string($value)) {
    //         $value = strtolower($value);
    //     }

    //     return parent::setAttribute($key, $value);
    // }
}
