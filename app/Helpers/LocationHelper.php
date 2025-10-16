<?php

namespace App\Helpers;

use App\Models\Country;
use App\Models\County;
use App\Models\City;
use GuzzleHttp\Client;
use Exception;

class LocationHelper
{
    public static function formatAddress($request)
    {
        $countryName = Country::find($request->input('country_id'))->name ?? '';
        $countyName = County::find($request->input('county_id'))->name ?? '';
        $cityName = City::find($request->input('city_id'))->name ?? '';

        $address1 = $request->input('address1', '');
        $address2 = $request->input('address2', '');
        $zipCode = $request->input('zip_code', '');

        return $address1 . ', ' 
        . ($address2 ? $address2 . ', ' : '') 
        . $zipCode . ', ' 
        . $cityName . ', ' 
        . $countyName . ', ' 
        . $countryName;
    }

    public static function getCoordinatesFromAddress($address)
    {
        $client = new Client();
        $apiKey = env('GOOGLE_MAPS_API_KEY');
        $url = "https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($address) . "&key=" . $apiKey;

        try {
            $response = $client->get($url);
            $data = json_decode($response->getBody()->getContents(), true);

            if ($data['status'] === 'OK') {
                $location = $data['results'][0]['geometry']['location'];
                return [
                    'latitude' => $location['lat'],
                    'longitude' => $location['lng'],
                ];
            }

            return null;
        } catch (Exception $e) {
            return null;
        }
    }
}
