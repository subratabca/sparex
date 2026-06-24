<?php

namespace App\Http\Controllers\Frontend\Meal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\CustomerMealLocation;
use App\Models\MealOrderItem;
use Carbon\Carbon;
use Exception;

class MealPlanSetupController extends Controller
{
    /**
     * The Meal Plan Setup page — same search/add flow as the Meal Plan page,
     * with a delivery location attached per (date, meal type).
     */
    public function index()
    {
        return view('frontend.pages.meal-plan-setup.index');
    }

    /**
     * Suggested weekly plan = clone the customer's LAST multi-address order onto the
     * next N consecutive days (N = number of days that order covered), keeping the same
     * meal types, foods, restaurants, locations and times. If the customer has no
     * multi-address order, returns source 'none' and the page falls back to the
     * health-based suggestion.
     */
    public function getSetupSuggestion(Request $request)
    {
        try {
            $customerId = $request->header('id');

            // most recent order that has per-group (multi-address) shipping rows
            $lastOrderId = DB::table('meal_orders')
                ->where('meal_orders.customer_id', $customerId)
                ->whereExists(function ($q) {
                    $q->select(DB::raw(1))
                      ->from('meal_shipping_addresses')
                      ->whereColumn('meal_shipping_addresses.meal_order_id', 'meal_orders.id')
                      ->whereNotNull('meal_shipping_addresses.meal_type_id');
                })
                ->orderByDesc('meal_orders.id')
                ->value('meal_orders.id');

            if (!$lastOrderId) {
                return response()->json(['status' => 'success', 'source' => 'none', 'days' => []], 200);
            }

            $items = MealOrderItem::with([
                    'product:id,name,image,price,discount_price',
                    'client:id,firstName,lastName',
                    'mealType:id,name',
                    'deliveryAddress:id,location_id',
                    'deliveryAddress.location:id,label,city_id',
                    'deliveryAddress.location.city:id,name',
                ])
                ->where('meal_order_id', $lastOrderId)
                ->orderBy('meal_date')
                ->orderBy('meal_type_id')
                ->get();

            if ($items->isEmpty()) {
                return response()->json(['status' => 'success', 'source' => 'none', 'days' => []], 200);
            }

            // distinct ordered dates → shift the block onto the next N days.
            // Start at lastDate+1, but never in the past (cart needs future dates) → max(lastDate+1, tomorrow).
            $dates     = $items->pluck('meal_date')->map(fn($d) => (string) $d)->unique()->sort()->values();
            $lastDate  = Carbon::parse($dates->last());
            $startBase = $lastDate->copy()->addDay();
            $tomorrow  = Carbon::tomorrow();
            $start     = $startBase->greaterThan($tomorrow) ? $startBase : $tomorrow;

            $dateMap = [];
            foreach ($dates as $i => $d) {
                $dateMap[(string) $d] = $start->copy()->addDays($i)->format('Y-m-d');
            }

            $days = [];
            foreach ($items->groupBy('meal_date') as $origDate => $dateItems) {
                $groups = [];
                foreach ($dateItems->groupBy('meal_type_id') as $mtId => $typeItems) {
                    $first = $typeItems->first();
                    $loc   = $first->deliveryAddress->location ?? null;

                    $groups[] = [
                        'meal_type_id' => (int) $mtId,
                        'meal_type'    => $first->mealType->name ?? '',
                        'meal_time'    => $first->meal_time,
                        'location'     => $loc ? [
                            'id'    => $loc->id,
                            'label' => $loc->label,
                            'city'  => $loc->city->name ?? null,
                        ] : null,
                        'items'        => $typeItems->map(fn($it) => [
                            'product'  => $it->product,
                            'client'   => $it->client ? [
                                'id'   => $it->client->id,
                                'name' => trim(($it->client->firstName ?? '') . ' ' . ($it->client->lastName ?? '')),
                            ] : null,
                            'quantity' => $it->quantity,
                        ])->values(),
                    ];
                }
                $days[] = ['date' => $dateMap[(string) $origDate], 'groups' => $groups];
            }

            return response()->json(['status' => 'success', 'source' => 'last_order', 'days' => $days], 200);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * List the logged-in customer's saved delivery locations.
     */
    public function getMyLocations(Request $request)
    {
        try {
            $customerId = $request->header('id');

            $locations = CustomerMealLocation::with(['country:id,name', 'county:id,name', 'city:id,name'])
                ->where('customer_id', $customerId)
                ->orderByDesc('is_default')
                ->orderBy('label')
                ->get();

            return response()->json(['status' => 'success', 'data' => $locations], 200);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Store a new delivery location for the logged-in customer.
     */
    public function storeLocation(Request $request)
    {
        try {
            $validated = $request->validate([
                'label'      => 'required|string|max:100',
                'name'       => 'nullable|string|max:150',
                'phone'      => 'nullable|string|max:30',
                'address1'   => 'required|string|max:255',
                'address2'   => 'nullable|string|max:255',
                'zip_code'   => 'required|string|max:50',
                'country_id' => 'required|integer|exists:countries,id',
                'county_id'  => 'required|integer|exists:counties,id',
                'city_id'    => 'required|integer|exists:cities,id',
                'latitude'   => 'nullable|numeric|between:-90,90',
                'longitude'  => 'nullable|numeric|between:-180,180',
                'is_default' => 'nullable|boolean',
            ]);

            $validated['customer_id'] = $request->header('id');

            $location = CustomerMealLocation::create($validated);

            return response()->json([
                'status'  => 'success',
                'message' => 'Location added successfully.',
                'data'    => $location->load(['country:id,name', 'county:id,name', 'city:id,name']),
            ], 201);
        } catch (ValidationException $e) {
            return response()->json(['status' => 'failed', 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
