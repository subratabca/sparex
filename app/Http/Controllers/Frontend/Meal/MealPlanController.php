<?php
namespace App\Http\Controllers\Frontend\Meal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use App\Helpers\JWTToken;
use App\Models\MealKeyword;
use App\Models\Product;
use App\Models\MealOrderItem;
use Exception;
use Carbon\Carbon;


class MealPlanController extends Controller
{
    public function mealPlanPage()
    {
        return view('frontend.pages.meal-plan.index');
    }
    
    public function getMealKeywordByType($mealTypeId)
    {
        try {
            $datas = MealKeyword::where('meal_type_id', $mealTypeId)->get();

            return response()->json([
                'status' => 'success',
                'data' => $datas
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function searchProducts(Request $request)
    {
        try {
            $request->validate([
                'keywords' => 'nullable|array',
                'keywords.*' => 'string|max:255',
                'meal_type_id' => 'nullable|integer|exists:meal_types,id',
                'latitude' => 'nullable|numeric|between:-90,90',
                'longitude' => 'nullable|numeric|between:-180,180',
                'page' => 'nullable|integer|min:1',
            ]);

            $keywords   = $request->input('keywords', []);
            if (!is_array($keywords)) $keywords = [$keywords];

            $mealTypeId = $request->input('meal_type_id');
            $latitude   = $request->input('latitude');
            $longitude  = $request->input('longitude');
            $radius     = 10;

            $currentDate = Carbon::now(new \DateTimeZone('Asia/Dhaka'));

            $baseQuery = Product::with(['city', 'client', 'mealTypes', 'category','nutrient'])
                ->whereHas('category', fn($q) => $q->whereRaw('LOWER(categories.name) = ?', ['food']))
                ->whereHas('mealTypes')
                ->where('expire_date', '>=', $currentDate)
                ->where(fn($q) => $q->where('products.status', 'published')->orWhere('products.status', 'processing'));

            // ✅ Keyword filter
            if (!empty($keywords)) {
                $baseQuery->where(function ($q) use ($keywords) {
                    foreach ($keywords as $word) {
                        $like = "%{$word}%";
                        $q->orWhere('products.name', 'like', $like)
                          ->orWhereHas('mealTypes', function ($mtq) use ($like) {
                              $mtq->where('meal_types.name', 'like', $like);
                          });
                    }
                });
            }

            // ✅ Meal type filter
            if (!empty($mealTypeId)) {
                $baseQuery->whereHas('mealTypes', fn($q) => $q->where('meal_types.id', $mealTypeId));
            }

            // ✅ If location provided, use distance sorting
            if ($latitude !== null && $longitude !== null) {
                $locationProducts = (clone $baseQuery)
                    ->selectRaw("
                        products.*,
                        (6371 * acos(
                            cos(radians(?)) * cos(radians(products.latitude)) * 
                            cos(radians(products.longitude) - radians(?)) + 
                            sin(radians(?)) * sin(radians(products.latitude))
                        )) AS distance
                    ", [$latitude, $longitude, $latitude])
                    ->having('distance', '<=', $radius)
                    ->orderBy('distance', 'asc')
                    ->get();

                $remainingProducts = (clone $baseQuery)
                    ->whereNotIn('products.id', $locationProducts->pluck('id')->toArray())
                    ->latest()
                    ->get();

                $allProducts = $locationProducts->merge($remainingProducts);
            } else {
                $allProducts = $baseQuery->latest()->get();
            }

            // ✅ Transform data
            $allProducts->transform(function ($product) {
                $client = $product->client;

                // Prepare nutrient data
                $nutrient = null;
                $hasNutrient = false;
                if ($product->nutrient) {
                    $nutrient = $product->nutrient->only([
                        'calories', 'calories_unit',
                        'protein', 'protein_unit',
                        'fat', 'fat_unit',
                        'carbohydrates', 'carbohydrates_unit',
                        'fiber', 'fiber_unit',
                        'sugar', 'sugar_unit',
                        'cholesterol', 'cholesterol_unit',
                        'sodium', 'sodium_unit',
                        'vitamin_a', 'vitamin_a_unit',
                        'vitamin_c', 'vitamin_c_unit',
                        'calcium', 'calcium_unit',
                        'iron', 'iron_unit'
                    ]);

                    // Check if at least one nutrient has a value
                    $hasNutrient = collect($nutrient)->some(fn($val) => $val !== null && $val !== '');
                }


                return (object)[
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'price' => $product->price,
                    'discount_price' => $product->discount_price,
                    'is_free' => $product->is_free,
                    'has_discount_price' => $product->has_discount_price,
                    'weight' => $product->weight,
                    'status' => $product->status,
                    'progress' => $product->progress,
                    'address1' => $product->address1,
                    'image' => $product->image,

                    'category' => $product->category ? [
                        'id' => $product->category->id,
                        'name' => $product->category->name,
                    ] : null,
                    
                    'client_info' => $client ? [
                        'id' => $client->id,
                        'first_name' => $client->firstName ?? '',
                        'last_name' => $client->lastName ?? '',
                    ] : null,

                    'meal_types' => $product->mealTypes->map(fn($mt) => [
                        'id' => $mt->id,
                        'name' => $mt->name
                    ])->values(),

                    // ✅ Include nutrient data and flag
                    'nutrients' => $nutrient,
                    'has_nutrient' => $hasNutrient,
                ];
            });

            // ✅ Pagination
            $page = LengthAwarePaginator::resolveCurrentPage();
            $perPage = 6;
            $paginatedProducts = new LengthAwarePaginator(
                $allProducts->forPage($page, $perPage)->values(),
                $allProducts->count(),
                $perPage,
                $page,
                ['path' => LengthAwarePaginator::resolveCurrentPath()]
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Search Successful',
                'products' => $paginatedProducts->toArray(),
                'total' => $allProducts->count()
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function mealDetailsPage()
    {
        return view('frontend.pages.meal-plan.meal-details');
    }

    public function getMealDetails(Request $request, $id)
    {
        try {
            $product = Product::with('productImages', 'mealTypes', 'client', 'variants', 'category', 'brand', 'country', 'county', 'city','nutrient')->find($id);

            if (!$product) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Product not found'
                ], 404);
            }

            $showAvailability = false;
            if (
                $product->category &&
                strtolower($product->category->name) === 'food' &&
                !$product->is_free &&
                $product->mealTypes &&
                $product->mealTypes->count() > 0
            ) {
                $showAvailability = true;
            }

            // ✅ Prepare nutrient data for frontend
            $nutrientData = null;
            if ($product->nutrient) {
                $nutrientData = $product->nutrient->only([
                    'calories', 'calories_unit',
                    'protein', 'protein_unit',
                    'fat', 'fat_unit',
                    'carbohydrates', 'carbohydrates_unit',
                    'fiber', 'fiber_unit',
                    'sugar', 'sugar_unit',
                    'cholesterol', 'cholesterol_unit',
                    'sodium', 'sodium_unit',
                    'vitamin_a', 'vitamin_a_unit',
                    'vitamin_c', 'vitamin_c_unit',
                    'calcium', 'calcium_unit',
                    'iron', 'iron_unit'
                ]);
            }

            return response()->json([
                'status' => 'success',
                'data' => $product,
                'nutrients' => $nutrientData, 
                'showAvailability' => $showAvailability
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function generateMealSuggestion(Request $request)
    {
        try {
            $validated = $request->validate([
                'gender'      => 'required|in:male,female,other',
                'age'         => 'required|integer|min:1|max:120',
                'weight'      => 'required|numeric|min:1|max:500',   // kg
                'height'      => 'required|numeric|min:30|max:300',  // cm
                'description' => 'nullable|string|max:1000',
                'period'      => 'nullable|in:last_week,last_month',
            ]);

            $customerId = $request->header('id');
            $period     = $validated['period'] ?? 'last_week';

            // ===== 1. Calculate BMI + recommended daily calorie target =====
            $heightM = $validated['height'] / 100;
            $bmi     = round($validated['weight'] / ($heightM * $heightM), 1);
            $bmiCategory = $this->getBmiCategory($bmi);

            // Mifflin-St Jeor BMR
            $bmr = (10 * $validated['weight'])
                 + (6.25 * $validated['height'])
                 - (5 * $validated['age'])
                 + ($validated['gender'] === 'male' ? 5 : -161);

            $maintenanceCalories = round($bmr * 1.4); // light activity factor

            // Adjust target based on BMI category
            $targetCalories = match ($bmiCategory) {
                'Underweight' => $maintenanceCalories + 300, // gain
                'Overweight', 'Obese' => max(1200, $maintenanceCalories - 400), // lose
                default => $maintenanceCalories, // maintain
            };

            // ===== 2. Pull past order history window =====
            $start = $period === 'last_month'
                ? Carbon::now()->subMonth()->startOfDay()
                : Carbon::now()->subWeek()->startOfDay();
            $end = Carbon::now()->endOfDay();

            $pastItems = \App\Models\MealOrderItem::with(['product.nutrient', 'mealType'])
                ->whereHas('mealOrder', fn($q) =>
                    $q->where('customer_id', $customerId)
                      ->whereBetween('created_at', [$start, $end])
                )
                ->get();

            // ===== 3. Analyse history =====
            $analysis = $this->analysePastOrders($pastItems);

            // ===== 4. Build suggestions per meal type from products user has access to =====
            $suggestions = $this->buildSuggestions($analysis, $targetCalories);

            return response()->json([
                'status' => 'success',
                'data'   => [
                    'bmi'            => $bmi,
                    'bmi_category'   => $bmiCategory,
                    'target_calories'=> $targetCalories,
                    'period'         => $period,
                    'analysis'       => $analysis,
                    'suggestions'    => $suggestions,
                    'has_history'    => $pastItems->isNotEmpty(),
                ],
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'status'  => 'failed',
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            \Log::error('generateMealSuggestion: ' . $e->getMessage());
            return response()->json([
                'status'  => 'failed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // ===== BMI category =====
    private function getBmiCategory(float $bmi): string
    {
        return match (true) {
            $bmi < 18.5  => 'Underweight',
            $bmi < 25    => 'Normal',
            $bmi < 30    => 'Overweight',
            default      => 'Obese',
        };
    }

    // ===== Analyse past order items =====
    private function analysePastOrders($pastItems): array
    {
        if ($pastItems->isEmpty()) {
            return [
                'total_orders_items'   => 0,
                'avg_daily_calories'   => 0,
                'avg_protein'          => 0,
                'avg_fat'              => 0,
                'avg_carbohydrates'    => 0,
                'top_products'         => [],
                'meal_type_calories'   => [],
                'distinct_days'        => 0,
            ];
        }

        // Group by date to get per-day totals
        $byDate = $pastItems->groupBy(fn($item) =>
            Carbon::parse($item->meal_date)->format('Y-m-d')
        );

        $distinctDays = $byDate->count();

        $sumCalories = $sumProtein = $sumFat = $sumCarbs = 0;
        $mealTypeCalories = [];
        $productFrequency = [];

        foreach ($pastItems as $item) {
            $n   = $item->product->nutrient ?? null;
            $qty = $item->quantity;

            $cal   = ($n->calories       ?? 0) * $qty;
            $prot  = ($n->protein        ?? 0) * $qty;
            $fat   = ($n->fat            ?? 0) * $qty;
            $carbs = ($n->carbohydrates  ?? 0) * $qty;

            $sumCalories += $cal;
            $sumProtein  += $prot;
            $sumFat      += $fat;
            $sumCarbs    += $carbs;

            // Meal-type breakdown
            $mtName = $item->mealType->name ?? 'Other';
            $mealTypeCalories[$mtName] = ($mealTypeCalories[$mtName] ?? 0) + $cal;

            // Product frequency for "most ordered"
            if ($item->product) {
                $pid = $item->product->id;
                if (!isset($productFrequency[$pid])) {
                    $productFrequency[$pid] = [
                        'id'       => $pid,
                        'name'     => $item->product->name,
                        'image'    => $item->product->image,
                        'calories' => $n->calories ?? 0,
                        'count'    => 0,
                    ];
                }
                $productFrequency[$pid]['count'] += $qty;
            }
        }

        // Top 5 most-ordered products
        $topProducts = collect($productFrequency)
            ->sortByDesc('count')
            ->take(5)
            ->values()
            ->toArray();

        return [
            'total_order_items'  => $pastItems->count(),
            'distinct_days'      => $distinctDays,
            'avg_daily_calories' => $distinctDays > 0 ? round($sumCalories / $distinctDays) : 0,
            'avg_protein'        => $distinctDays > 0 ? round($sumProtein / $distinctDays, 1) : 0,
            'avg_fat'            => $distinctDays > 0 ? round($sumFat / $distinctDays, 1) : 0,
            'avg_carbohydrates'  => $distinctDays > 0 ? round($sumCarbs / $distinctDays, 1) : 0,
            'top_products'       => $topProducts,
            'meal_type_calories' => $mealTypeCalories,
        ];
    }

    // ===== Build product suggestions per meal type =====
    private function buildSuggestions(array $analysis, int $targetCalories): array
    {
        // Calorie split per meal type (typical distribution)
        $split = [
            'Breakfast' => 0.25,
            'Lunch'     => 0.35,
            'Snacks'    => 0.15,
            'Dinner'    => 0.25,
        ];

        $currentDate = Carbon::now(new \DateTimeZone('Asia/Dhaka'));
        $suggestions = [];

        foreach ($split as $mealTypeName => $ratio) {
            $mealCalorieTarget = round($targetCalories * $ratio);

            // Find published food products for this meal type,
            // ordered by closeness to the per-meal calorie target
            $products = Product::with(['nutrient', 'mealTypes', 'client:id,firstName,lastName'])
                ->whereHas('category', fn($q) =>
                    $q->whereRaw('LOWER(categories.name) = ?', ['food'])
                )
                ->whereHas('mealTypes', fn($q) =>
                    $q->whereRaw('LOWER(meal_types.name) = ?', [strtolower($mealTypeName)])
                )
                ->where('expire_date', '>=', $currentDate)
                ->whereIn('products.status', ['published', 'processing'])
                ->whereHas('nutrient', fn($q) => $q->whereNotNull('calories'))
                ->get();

            // Sort by closeness to calorie target, pick top 3
            $picked = $products
                ->sortBy(fn($p) => abs(($p->nutrient->calories ?? 0) - $mealCalorieTarget))
                ->take(3)
                ->map(fn($p) => [
                    'id'           => $p->id,
                    'name'         => $p->name,
                    'image'        => $p->image,
                    'price'        => $p->price,
                    'calories'     => $p->nutrient->calories ?? 0,
                    'calories_unit'=> $p->nutrient->calories_unit ?? 'kcal',
                    'protein'      => $p->nutrient->protein ?? null,
                    'client_name'  => $p->client
                        ? trim($p->client->firstName . ' ' . $p->client->lastName)
                        : null,
                ])->values()->toArray();

            $suggestions[] = [
                'meal_type'       => $mealTypeName,
                'calorie_target'  => $mealCalorieTarget,
                'products'        => $picked,
            ];
        }

        return $suggestions;
    }

}
