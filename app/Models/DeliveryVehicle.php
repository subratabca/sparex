<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryVehicle extends Model
{
    protected $fillable = ['delivery_id','image','vehicle_type','registration_number','vehicle_color','vehicle_brand','vehicle_model','is_active'];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    // Vehicle Types
    const TYPE_BIKE = 'bike';
    const TYPE_SCOOTER = 'scooter';
    const TYPE_CAR = 'car';
    const TYPE_BICYCLE = 'bicycle';
    const TYPE_WALKING = 'walking';

    public static function getVehicleTypes()
    {
        return [
            self::TYPE_BIKE => 'Bike',
            self::TYPE_SCOOTER => 'Scooter',
            self::TYPE_CAR => 'Car',
            self::TYPE_BICYCLE => 'Bicycle',
            self::TYPE_WALKING => 'Walking',
        ];
    }

    // Relationships
    public function delivery()
    {
        return $this->belongsTo(User::class, 'delivery_id');
    }

    public function getVehicleTypeLabelAttribute()
    {
        return self::getVehicleTypes()[$this->vehicle_type] ?? ucfirst($this->vehicle_type);
    }

    public function getVehicleInfoAttribute()
    {
        $info = [];
        
        if ($this->vehicle_brand) {
            $info[] = $this->vehicle_brand;
        }
        
        if ($this->vehicle_model) {
            $info[] = $this->vehicle_model;
        }
        
        if ($this->vehicle_color) {
            $info[] = $this->vehicle_color;
        }
        
        if ($this->registration_number) {
            $info[] = "({$this->registration_number})";
        }
        
        return !empty($info) ? implode(' ', $info) : 'No vehicle info';
    }
}