<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class BaseUserModel extends Authenticatable
{
    /**
     * Convert all string attributes to lowercase before saving
     */
    public function setAttribute($key, $value)
    {
        if (is_string($value)) {
            $value = strtolower($value);
        }

        return parent::setAttribute($key, $value);
    }
}
