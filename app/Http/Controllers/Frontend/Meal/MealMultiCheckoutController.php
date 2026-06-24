<?php

namespace App\Http\Controllers\Frontend\Meal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MealCart;
use App\Models\MealDeliveryCharge;
use App\Helpers\MealOrderHelper;
use Exception;

class MealMultiCheckoutController extends Controller
{
    /**
     * Multi-address checkout page (one order → many delivery locations).
     */
    public function index()
    {
        return view('frontend.pages.meal-checkout-multi.index');
    }

    /**
     * Build the multi-address checkout payload: cart grouped by date → meal type,
     * each group with its delivery location + items + per-group delivery charge.
     * Delivery charge uses the SAME rule as the single checkout — one charge per
     * distinct restaurant within a (date, meal type) slot — but distance is measured
     * to that group's own location.
     */
    public function getMultiCheckoutData(Request $request)
    {
        try {
            $customerId = $request->header('id');

            $items = MealCart::with([
                    'product:id,name,image,price,discount_price',
                    'mealType:id,name',
                    'client:id,firstName,lastName,city_id,address1,zip_code',
                    'location:id,label,name,phone,address1,address2,zip_code,country_id,county_id,city_id,latitude,longitude',
                    'location.city:id,name',
                ])
                ->where('customer_id', $customerId)
                ->whereNotNull('location_id')          // multi-address items only
                ->orderBy('meal_date')
                ->orderBy('meal_type_id')
                ->get();

            if ($items->isEmpty()) {
                return response()->json(['status' => 'error', 'message' => 'No multi-address items in your cart.'], 404);
            }

            $taxRate        = (float) config('services.tax_rate', 0.20);
            $serviceFeeRate = (float) config('services.service_fee', 0.05);

            $days        = [];
            $subtotalAll = 0;
            $deliveryAll = 0;
            $totalItems  = 0;

            foreach ($items->groupBy('meal_date') as $date => $dateItems) {
                $groupsForDate = [];

                foreach ($dateItems->groupBy('meal_type_id') as $mealTypeId => $typeItems) {
                    $first        = $typeItems->first();
                    $location     = $first->location;
                    $mealTypeName = $first->mealType->name ?? 'Unknown';

                    // items + group subtotal
                    $groupSubtotal = 0;
                    $itemsOut      = [];
                    foreach ($typeItems as $it) {
                        $lineTotal      = $it->quantity * $it->unit_price;
                        $groupSubtotal += $lineTotal;
                        $totalItems    += $it->quantity;
                        $itemsOut[] = [
                            'id'         => $it->id,
                            'product'    => $it->product,
                            'quantity'   => $it->quantity,
                            'unit_price' => $it->unit_price,
                            'line_total' => round($lineTotal, 2),
                            'client'     => $it->client ? [
                                'id'   => $it->client->id,
                                'name' => trim(($it->client->firstName ?? '') . ' ' . ($it->client->lastName ?? '')),
                            ] : null,
                        ];
                    }

                    // delivery charge: one per distinct restaurant → distance to THIS group's location
                    $groupDelivery = 0;
                    if ($location) {
                        $dest = [
                            'city_id'  => $location->city_id,
                            'address1' => $location->address1,
                            'zip_code' => $location->zip_code,
                        ];
                        $rate        = MealDeliveryCharge::where('meal_type_id', $mealTypeId)->first();
                        $seenClients = [];
                        foreach ($typeItems as $it) {
                            $client = $it->client;
                            if (!$client || in_array($client->id, $seenClients, true)) continue;
                            $seenClients[] = $client->id;
                            if (!$client->city_id || !$client->address1 || !$client->zip_code || !$rate) continue;

                            $origin   = ['city_id' => $client->city_id, 'address1' => $client->address1, 'zip_code' => $client->zip_code];
                            $distance = MealOrderHelper::getDistanceBetweenLocations($origin, $dest);
                            if ($distance === null) continue;

                            $groupDelivery += MealOrderHelper::getChargeByDistance($rate, $distance);
                        }
                    }

                    $subtotalAll += $groupSubtotal;
                    $deliveryAll += $groupDelivery;

                    $groupsForDate[] = [
                        'meal_type_id'    => (int) $mealTypeId,
                        'meal_type'       => $mealTypeName,
                        'meal_time'       => $first->meal_time,
                        'location'        => $location ? [
                            'id'       => $location->id,
                            'label'    => $location->label,
                            'name'     => $location->name,
                            'phone'    => $location->phone,
                            'address1' => $location->address1,
                            'address2' => $location->address2,
                            'zip_code' => $location->zip_code,
                            'city'     => $location->city->name ?? null,
                        ] : null,
                        'items'           => $itemsOut,
                        'subtotal'        => round($groupSubtotal, 2),
                        'delivery_charge' => round($groupDelivery, 2),
                    ];
                }

                $days[] = ['date' => $date, 'groups' => $groupsForDate];
            }

            $tax        = round($subtotalAll * $taxRate, 2);
            $serviceFee = round($subtotalAll * $serviceFeeRate, 2);
            $total      = round($subtotalAll + $tax + $serviceFee + $deliveryAll, 2);

            return response()->json([
                'status' => 'success',
                'data'   => [
                    'days'    => $days,
                    'summary' => [
                        'subtotal'         => round($subtotalAll, 2),
                        'tax'              => $tax,
                        'tax_rate'         => $taxRate,
                        'service_fee'      => $serviceFee,
                        'service_fee_rate' => $serviceFeeRate,
                        'delivery_total'   => round($deliveryAll, 2),
                        'total'            => $total,
                        'total_items'      => $totalItems,
                    ],
                ],
            ]);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
