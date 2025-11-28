<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    /**
     * Automatically convert all string attributes to lowercase
     */
    public function setAttribute($key, $value)
    {
        // Convert to lowercase if it's a string value
        if (is_string($value)) {
            $value = strtolower($value);
        }
        
        return parent::setAttribute($key, $value);
    }
}