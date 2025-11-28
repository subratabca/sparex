<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductNutrient extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',

        // Macronutrients
        'calories', 'calories_unit',
        'protein', 'protein_unit',
        'fat', 'fat_unit',
        'carbohydrates', 'carbohydrates_unit',
        'fiber', 'fiber_unit',
        'sugar', 'sugar_unit',
        'cholesterol', 'cholesterol_unit',
        'sodium', 'sodium_unit',

        // Micronutrients
        'vitamin_a', 'vitamin_a_unit',
        'vitamin_c', 'vitamin_c_unit',
        'calcium', 'calcium_unit',
        'iron', 'iron_unit',
    ];

    /**
     * Each nutrient record belongs to a product.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
