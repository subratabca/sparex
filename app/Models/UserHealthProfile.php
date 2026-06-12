<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserHealthProfile extends Model
{
    protected $fillable = [
        'user_id', 'gender', 'age', 'weight', 'height', 'description',
        'conditions', 'activity_level', 'goal', 'allergies', 'dietary_preference', 'period',
    ];

    // store/retrieve JSON arrays natively
    protected $casts = [
        'conditions' => 'array',
        'allergies'  => 'array',
    ];

    // The condition options offered in the UI (value => label)
    public const CONDITIONS = [
        'diabetes'        => 'Diabetes',
        'hypertension'    => 'High blood pressure',
        'high_cholesterol'=> 'High cholesterol',
        'heart_disease'   => 'Heart disease',
        'kidney_disease'  => 'Kidney disease',
        'thyroid'         => 'Thyroid disorder',
        'celiac'          => 'Celiac / gluten intolerance',
        'lactose'         => 'Lactose intolerance',
        'ibs'             => 'IBS / digestive issues',
        'anemia'          => 'Anaemia',
        'obesity'         => 'Obesity',
        'pcos'            => 'PCOS',
    ];

    public const ACTIVITY_LEVELS = [
        'sedentary'   => 'Sedentary (little/no exercise)',
        'light'       => 'Light (1–3 days/week)',
        'moderate'    => 'Moderate (3–5 days/week)',
        'active'      => 'Active (6–7 days/week)',
        'very_active' => 'Very active (physical job/training)',
    ];

    public const GOALS = [
        'lose'     => 'Lose weight',
        'maintain' => 'Maintain weight',
        'gain'     => 'Gain weight',
    ];

    public const DIETS = [
        'none'        => 'No preference',
        'vegetarian'  => 'Vegetarian',
        'vegan'       => 'Vegan',
        'pescatarian' => 'Pescatarian',
        'halal'       => 'Halal',
        'kosher'      => 'Kosher',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}