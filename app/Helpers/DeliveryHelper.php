<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DeliveryHelper
{
    /**
     * Calculate distance between two locations using Google Maps Distance Matrix API
     *
     * @param array $clientAddress Contains: address1, zip_code, city_id (optional)
     * @param array $shippingAddress Contains: address1, zip_code, city_id (optional)
     * @return float|null Distance in kilometers, or null if calculation fails
     */
    public static function getDistanceBetweenLocations($clientAddress, $shippingAddress)
    {
        $apiKey = config('services.google_maps.api_key');

        if (!$apiKey) {
            Log::error('Google Maps API key not configured');
            return null;
        }

        try {
            // Construct origin & destination with full addresses
            $origin = urlencode("{$clientAddress['address1']}, {$clientAddress['zip_code']}");
            $destination = urlencode("{$shippingAddress['address1']}, {$shippingAddress['zip_code']}");

            // Build URL for Google Distance Matrix API
            $url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins={$origin}&destinations={$destination}&mode=driving&units=metric&key={$apiKey}";

            $response = Http::timeout(10)->get($url);
            $data = $response->json();

            if (isset($data['rows'][0]['elements'][0]['status']) && 
                $data['rows'][0]['elements'][0]['status'] === 'OK') {
                
                // Distance in meters
                $distanceMeters = $data['rows'][0]['elements'][0]['distance']['value'];
                // Convert to KM
                $distanceKm = $distanceMeters / 1000;
                
                Log::info("Distance calculated: {$distanceKm} km from {$clientAddress['address1']} to {$shippingAddress['address1']}");
                return round($distanceKm, 2);
            }

            Log::warning("Distance API returned status: " . ($data['rows'][0]['elements'][0]['status'] ?? 'UNKNOWN'));
            return null;

        } catch (\Exception $e) {
            Log::error("Distance calculation error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Calculate delivery charge based on distance using default pricing
     *
     * @param float $distance Distance in kilometers
     * @return float Delivery charge amount
     */
    public static function getDefaultDeliveryCharge($distance)
    {
        if ($distance <= 2) {
            return 5.00;
        } elseif ($distance <= 5) {
            return 8.00;
        } elseif ($distance <= 10) {
            return 12.00;
        } else {
            // $15 for first 10km + $2 per additional km
            return 15.00 + (ceil($distance - 10) * 2);
        }
    }

    /**
     * Calculate delivery charge based on distance and meal delivery charge settings
     *
     * @param float $distance Distance in kilometers
     * @param \App\Models\MealDeliveryCharge|null $deliveryCharge Meal delivery charge settings
     * @return float Delivery charge amount
     */
    public static function calculateDeliveryCharge($distance, $deliveryCharge = null)
    {
        // If no custom delivery charge settings, use default
        if (!$deliveryCharge) {
            return self::getDefaultDeliveryCharge($distance);
        }

        // Use custom delivery charge settings
        if ($distance <= 2) {
            return $deliveryCharge->inside_city_2km;
        } elseif ($distance <= 5) {
            return $deliveryCharge->inside_city_5km;
        } elseif ($distance <= 10) {
            return $deliveryCharge->inside_city_10km;
        } else {
            return $deliveryCharge->inside_city_above_10km;
        }
    }

    public static function getDistanceCategory($distance)
    {
        if ($distance <= 2) {
            return 'inside_city_2km';
        } elseif ($distance <= 5) {
            return 'inside_city_5km';
        } elseif ($distance <= 10) {
            return 'inside_city_10km';
        } else {
            return 'inside_city_above_10km';
        }
    }

    /**
     * Calculate tax amount based on subtotal
     *
     * @param float $subtotal Subtotal amount
     * @param float|null $taxRate Custom tax rate (optional, defaults to config value)
     * @return float Tax amount
     */
    public static function calculateTax($subtotal, $taxRate = null)
    {
        $taxRate = $taxRate ?? (float) config('services.tax_rate', 0.10);
        return $subtotal * $taxRate;
    }
}