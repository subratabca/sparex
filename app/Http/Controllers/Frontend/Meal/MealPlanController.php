<?php
namespace App\Http\Controllers\Frontend\Meal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use App\Models\MealKeyword;
use App\Models\MealType;
use App\Models\Product;
use App\Models\MealOrderItem;
use App\Models\UserHealthProfile;
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
            $datas = MealKeyword::whereHas('mealTypes', function ($q) use ($mealTypeId) {
                $q->where('meal_types.id', $mealTypeId);
            })->get();
            return response()->json(['status' => 'success', 'data' => $datas], 200);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function searchProducts(Request $request)
    {
        try {
            $request->validate([
                'keywords'     => 'nullable|array',
                'keywords.*'   => 'string|max:255',
                'meal_type_id' => 'nullable|integer|exists:meal_types,id',
                'latitude'     => 'nullable|numeric|between:-90,90',
                'longitude'    => 'nullable|numeric|between:-180,180',
                'page'         => 'nullable|integer|min:1',
            ]);

            $keywords = $request->input('keywords', []);
            if (!is_array($keywords)) $keywords = [$keywords];

            $mealTypeId = $request->input('meal_type_id');
            $latitude   = $request->input('latitude');
            $longitude  = $request->input('longitude');
            $radius     = 10;

            $currentDate = Carbon::now(new \DateTimeZone('Asia/Dhaka'));

            $baseQuery = Product::with(['city', 'client', 'mealTypes', 'category', 'nutrient'])
                ->whereHas('category', fn($q) => $q->whereRaw('LOWER(categories.name) = ?', ['food']))
                ->whereHas('mealTypes')
                ->where('expire_date', '>=', $currentDate)
                ->where(fn($q) => $q->where('products.status', 'published')->orWhere('products.status', 'processing'));

            // Keyword / food-name filter (matches product name OR meal type name)
            if (!empty($keywords)) {
                $baseQuery->where(function ($q) use ($keywords) {
                    foreach ($keywords as $word) {
                        $like = "%{$word}%";
                        $q->orWhere('products.name', 'like', $like)
                          ->orWhereHas('mealTypes', fn($mtq) => $mtq->where('meal_types.name', 'like', $like));
                    }
                });
            }

            if (!empty($mealTypeId)) {
                $baseQuery->whereHas('mealTypes', fn($q) => $q->where('meal_types.id', $mealTypeId));
            }

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

            $allProducts->transform(function ($product) {
                $client      = $product->client;
                $nutrient    = null;
                $hasNutrient = false;

                if ($product->nutrient) {
                    $nutrient = $product->nutrient->only([
                        'calories', 'calories_unit', 'protein', 'protein_unit', 'fat', 'fat_unit',
                        'carbohydrates', 'carbohydrates_unit', 'fiber', 'fiber_unit', 'sugar', 'sugar_unit',
                        'cholesterol', 'cholesterol_unit', 'sodium', 'sodium_unit', 'vitamin_a', 'vitamin_a_unit',
                        'vitamin_c', 'vitamin_c_unit', 'calcium', 'calcium_unit', 'iron', 'iron_unit',
                    ]);
                    $hasNutrient = collect($nutrient)->some(fn($val) => $val !== null && $val !== '');
                }

                return (object) [
                    'id' => $product->id, 'name' => $product->name, 'description' => $product->description,
                    'price' => $product->price, 'discount_price' => $product->discount_price,
                    'is_free' => $product->is_free, 'has_discount_price' => $product->has_discount_price,
                    'weight' => $product->weight, 'status' => $product->status, 'progress' => $product->progress,
                    'address1' => $product->address1, 'image' => $product->image,
                    'category' => $product->category ? ['id' => $product->category->id, 'name' => $product->category->name] : null,
                    'client_info' => $client ? ['id' => $client->id, 'first_name' => $client->firstName ?? '', 'last_name' => $client->lastName ?? ''] : null,
                    'meal_types' => $product->mealTypes->map(fn($mt) => ['id' => $mt->id, 'name' => $mt->name])->values(),
                    'nutrients' => $nutrient, 'has_nutrient' => $hasNutrient,
                ];
            });

            $page    = LengthAwarePaginator::resolveCurrentPage();
            $perPage = 6;
            $paginated = new LengthAwarePaginator(
                $allProducts->forPage($page, $perPage)->values(),
                $allProducts->count(), $perPage, $page,
                ['path' => LengthAwarePaginator::resolveCurrentPath()]
            );

            return response()->json([
                'status'   => 'success',
                'message'  => 'Search Successful',
                'products' => $paginated->toArray(),
                'total'    => $allProducts->count(),
            ], 200);

        } catch (ValidationException $e) {
            return response()->json(['status' => 'failed', 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (Exception $e) {
            return response()->json(['status' => 'failed', 'error' => $e->getMessage()], 500);
        }
    }

    public function mealDetailsPage()
    {
        return view('frontend.pages.meal-plan.meal-details');
    }

    public function getMealDetails(Request $request, $id)
    {
        try {
            $product = Product::with('productImages', 'mealTypes', 'client', 'variants', 'category', 'brand', 'country', 'county', 'city', 'nutrient')->find($id);

            if (!$product) {
                return response()->json(['status' => 'failed', 'message' => 'Product not found'], 404);
            }

            $showAvailability = $product->category
                && strtolower($product->category->name) === 'food'
                && !$product->is_free
                && $product->mealTypes && $product->mealTypes->count() > 0;

            $nutrientData = null;
            if ($product->nutrient) {
                $nutrientData = $product->nutrient->only([
                    'calories', 'calories_unit', 'protein', 'protein_unit', 'fat', 'fat_unit',
                    'carbohydrates', 'carbohydrates_unit', 'fiber', 'fiber_unit', 'sugar', 'sugar_unit',
                    'cholesterol', 'cholesterol_unit', 'sodium', 'sodium_unit', 'vitamin_a', 'vitamin_a_unit',
                    'vitamin_c', 'vitamin_c_unit', 'calcium', 'calcium_unit', 'iron', 'iron_unit',
                ]);
            }

            return response()->json([
                'status' => 'success', 'data' => $product,
                'nutrients' => $nutrientData, 'showAvailability' => $showAvailability,
            ], 200);

        } catch (Exception $e) {
            return response()->json(['status' => 'failed', 'message' => 'An error occurred', 'error' => $e->getMessage()], 500);
        }
    }

    /* =========================================================
     | Health profile check (on page load)
     ========================================================= */
    public function getHealthProfile(Request $request)
    {
        try {
            $customerId = $request->header('id');
            $profile    = UserHealthProfile::where('user_id', $customerId)->first();

            if (!$profile) {
                // No profile → still suggest from past order history (if any)
                $data = $this->buildHistorySuggestionResponse($customerId);
                $data['has_profile'] = false;
                return response()->json(['status' => 'success', 'data' => $data], 200);
            }

            $data = $this->buildSuggestionResponse($customerId, [
                'gender'         => $profile->gender,
                'age'            => $profile->age,
                'weight'         => $profile->weight,
                'height'         => $profile->height,
                'description'    => $profile->description,
                'period'         => $profile->period,
                'conditions'     => $profile->conditions ?? [],
                'activity_level' => $profile->activity_level,
                'goal'           => $profile->goal,
            ]);

            $data['has_profile'] = true;
            $data['profile'] = [
                'gender' => $profile->gender, 'age' => $profile->age,
                'weight' => $profile->weight, 'height' => $profile->height,
                'description' => $profile->description, 'period' => $profile->period,
                'conditions' => $profile->conditions ?? [],
                'activity_level' => $profile->activity_level,
                'goal' => $profile->goal,
                'allergies' => $profile->allergies ?? [],
                'dietary_preference' => $profile->dietary_preference,
            ];

            return response()->json(['status' => 'success', 'data' => $data], 200);

        } catch (Exception $e) {
            Log::error('getHealthProfile: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /* =========================================================
     | Save health profile + generate suggestions
     ========================================================= */
    public function generateMealSuggestion(Request $request)
    {
        try {
            $validated = $request->validate([
                'gender'             => 'required|in:male,female,other',
                'age'                => 'required|integer|min:1|max:120',
                'weight'             => 'required|numeric|min:1|max:500',
                'height'             => 'required|numeric|min:30|max:300',
                'description'        => 'nullable|string|max:1000',
                'period'             => 'nullable|in:last_week,last_month',
                'conditions'         => 'nullable|array',
                'conditions.*'       => 'string|in:' . implode(',', array_keys(\App\Models\UserHealthProfile::CONDITIONS)),
                'activity_level'     => 'nullable|in:' . implode(',', array_keys(\App\Models\UserHealthProfile::ACTIVITY_LEVELS)),
                'goal'               => 'nullable|in:' . implode(',', array_keys(\App\Models\UserHealthProfile::GOALS)),
                'allergies'          => 'nullable|array',
                'allergies.*'        => 'string|max:100',
                'dietary_preference' => 'nullable|in:' . implode(',', array_keys(\App\Models\UserHealthProfile::DIETS)),
                'update_only'        => 'nullable|boolean',
            ]);

            $customerId = $request->header('id');
            $validated['period'] = $validated['period'] ?? 'last_week';

            UserHealthProfile::updateOrCreate(
                ['user_id' => $customerId],
                [
                    'gender'             => $validated['gender'],
                    'age'                => $validated['age'],
                    'weight'             => $validated['weight'],
                    'height'             => $validated['height'],
                    'description'        => $validated['description'] ?? null,
                    'period'             => $validated['period'],
                    'conditions'         => $validated['conditions'] ?? [],
                    'activity_level'     => $validated['activity_level'] ?? 'light',
                    'goal'               => $validated['goal'] ?? null,
                    'allergies'          => $validated['allergies'] ?? [],
                    'dietary_preference' => $validated['dietary_preference'] ?? null,
                ]
            );

            // "Update Health Info" only persists the profile — no plan regeneration
            if ($request->boolean('update_only')) {
                return response()->json([
                    'status'  => 'success',
                    'message' => 'Health information updated successfully.',
                    'data'    => ['has_profile' => true, 'update_only' => true],
                ], 200);
            }

            $data = $this->buildSuggestionResponse($customerId, $validated);

            return response()->json(['status' => 'success', 'data' => $data], 200);

        } catch (ValidationException $e) {
            return response()->json(['status' => 'failed', 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (Exception $e) {
            Log::error('generateMealSuggestion: ' . $e->getMessage());
            return response()->json(['status' => 'failed', 'message' => $e->getMessage()], 500);
        }
    }

    /* =========================================================
     | Shared: build full suggestion payload
     ========================================================= */
    private function buildSuggestionResponse1111($customerId, array $profile): array
    {
        $heightM     = $profile['height'] / 100;
        $bmi         = round($profile['weight'] / ($heightM * $heightM), 1);
        $bmiCategory = $this->getBmiCategory($bmi);

        $bmr = (10 * $profile['weight'])
             + (6.25 * $profile['height'])
             - (5 * $profile['age'])
             + ($profile['gender'] === 'male' ? 5 : -161);

        // activity multiplier replaces the flat 1.4
        $multipliers = [
            'sedentary' => 1.2, 'light' => 1.375, 'moderate' => 1.55,
            'active' => 1.725, 'very_active' => 1.9,
        ];
        $activity    = $profile['activity_level'] ?? 'light';
        $maintenance = round($bmr * ($multipliers[$activity] ?? 1.375));

        // explicit goal wins; otherwise fall back to BMI-based logic
        $goal = $profile['goal'] ?? null;
        $targetCalories = match ($goal) {
            'lose'     => max(1200, $maintenance - 400),
            'gain'     => $maintenance + 300,
            'maintain' => $maintenance,
            default    => match ($bmiCategory) {
                'Underweight'         => $maintenance + 300,
                'Overweight', 'Obese' => max(1200, $maintenance - 400),
                default               => $maintenance,
            },
        };

        // ... rest unchanged (history pull, analysis, weekly plan) ...

        return [
            'bmi'             => $bmi,
            'bmi_category'    => $bmiCategory,
            'target_calories' => $targetCalories,
            'period'          => $profile['period'] ?? 'last_week',
            'conditions'      => $profile['conditions'] ?? [],   // ← surface to UI
            'analysis'        => $analysis,
            'weekly_plan'     => $weeklyPlan,
            'has_history'     => $pastItems->isNotEmpty(),
        ];
    }

    /* =========================================================
     | Shared: build full suggestion payload
     ========================================================= */
    private function buildSuggestionResponse($customerId, array $profile): array
    {
        $heightM     = $profile['height'] / 100;
        $bmi         = round($profile['weight'] / ($heightM * $heightM), 1);
        $bmiCategory = $this->getBmiCategory($bmi);

        $bmr = (10 * $profile['weight'])
             + (6.25 * $profile['height'])
             - (5 * $profile['age'])
             + ($profile['gender'] === 'male' ? 5 : -161);

        // activity multiplier replaces the flat 1.4
        $multipliers = [
            'sedentary' => 1.2, 'light' => 1.375, 'moderate' => 1.55,
            'active' => 1.725, 'very_active' => 1.9,
        ];
        $activity    = $profile['activity_level'] ?? 'light';
        $maintenance = round($bmr * ($multipliers[$activity] ?? 1.375));

        // explicit goal wins; otherwise fall back to BMI-based logic
        $goal = $profile['goal'] ?? null;
        $targetCalories = match ($goal) {
            'lose'     => max(1200, $maintenance - 400),
            'gain'     => $maintenance + 300,
            'maintain' => $maintenance,
            default    => match ($bmiCategory) {
                'Underweight'         => $maintenance + 300,
                'Overweight', 'Obese' => max(1200, $maintenance - 400),
                default               => $maintenance,
            },
        };

        // ===== History pull (period window) =====
        $period = $profile['period'] ?? 'last_week';
        $start  = $period === 'last_month'
            ? Carbon::now()->subMonth()->startOfDay()
            : Carbon::now()->subWeek()->startOfDay();
        $end    = Carbon::now()->endOfDay();

        $pastItems = MealOrderItem::with(['product.nutrient', 'mealType'])
            ->whereHas('mealOrder', fn($q) =>
                $q->where('customer_id', $customerId)
                  ->whereBetween('created_at', [$start, $end])
            )
            ->get();

        // ===== Analysis + weekly plan =====
        $analysis   = $this->analysePastOrders($pastItems);
        $weeklyPlan = $this->buildWeeklyPlan($targetCalories);

        return [
            'bmi'             => $bmi,
            'bmi_category'    => $bmiCategory,
            'target_calories' => $targetCalories,
            'period'          => $period,
            'conditions'      => $profile['conditions'] ?? [],   // surfaced to UI
            'analysis'        => $analysis,
            'weekly_plan'     => $weeklyPlan,
            'has_history'     => $pastItems->isNotEmpty(),
        ];
    }

    private function getBmiCategory(float $bmi): string
    {
        return match (true) {
            $bmi < 18.5 => 'Underweight',
            $bmi < 25   => 'Normal',
            $bmi < 30   => 'Overweight',
            default     => 'Obese',
        };
    }

    private function analysePastOrders($pastItems): array
    {
        if ($pastItems->isEmpty()) {
            return [
                'total_order_items' => 0, 'distinct_days' => 0,
                'avg_daily_calories' => 0, 'avg_protein' => 0, 'avg_fat' => 0, 'avg_carbohydrates' => 0,
                'top_products' => [], 'meal_type_calories' => [],
            ];
        }

        $byDate       = $pastItems->groupBy(fn($item) => Carbon::parse($item->meal_date)->format('Y-m-d'));
        $distinctDays = $byDate->count();

        $sumCalories = $sumProtein = $sumFat = $sumCarbs = 0;
        $mealTypeCalories = [];
        $productFrequency = [];

        foreach ($pastItems as $item) {
            $n   = $item->product->nutrient ?? null;
            $qty = $item->quantity;

            $sumCalories += ($n->calories      ?? 0) * $qty;
            $sumProtein  += ($n->protein       ?? 0) * $qty;
            $sumFat      += ($n->fat           ?? 0) * $qty;
            $sumCarbs    += ($n->carbohydrates ?? 0) * $qty;

            $mtName = $item->mealType->name ?? 'Other';
            $mealTypeCalories[$mtName] = ($mealTypeCalories[$mtName] ?? 0) + (($n->calories ?? 0) * $qty);

            if ($item->product) {
                $pid = $item->product->id;
                if (!isset($productFrequency[$pid])) {
                    $productFrequency[$pid] = [
                        'id' => $pid, 'name' => $item->product->name,
                        'image' => $item->product->image, 'calories' => $n->calories ?? 0, 'count' => 0,
                    ];
                }
                $productFrequency[$pid]['count'] += $qty;
            }
        }

        return [
            'total_order_items'  => $pastItems->count(),
            'distinct_days'      => $distinctDays,
            'avg_daily_calories' => $distinctDays > 0 ? round($sumCalories / $distinctDays) : 0,
            'avg_protein'        => $distinctDays > 0 ? round($sumProtein / $distinctDays, 1) : 0,
            'avg_fat'            => $distinctDays > 0 ? round($sumFat / $distinctDays, 1) : 0,
            'avg_carbohydrates'  => $distinctDays > 0 ? round($sumCarbs / $distinctDays, 1) : 0,
            'top_products'       => collect($productFrequency)->sortByDesc('count')->take(5)->values()->toArray(),
            'meal_type_calories' => $mealTypeCalories,
        ];
    }

    // ===== Build a 7-day plan, by date & meal type =====
    private function buildWeeklyPlan(int $targetCalories): array
    {
        $split       = ['Breakfast' => 0.25, 'Lunch' => 0.35, 'Snacks' => 0.15, 'Dinner' => 0.25];
        $currentDate = Carbon::now(new \DateTimeZone('Asia/Dhaka'));

        // Pre-fetch product pools per meal type, sorted by closeness to that meal's calorie target
        $pools = [];
        foreach ($split as $mealTypeName => $ratio) {
            $mealTarget = (int) round($targetCalories * $ratio);
            $mealType   = MealType::whereRaw('LOWER(name) = ?', [strtolower($mealTypeName)])->first();

            $products = Product::with(['nutrient', 'client:id,firstName,lastName'])
                ->whereHas('category', fn($q) => $q->whereRaw('LOWER(categories.name) = ?', ['food']))
                ->whereHas('mealTypes', fn($q) => $q->whereRaw('LOWER(meal_types.name) = ?', [strtolower($mealTypeName)]))
                ->where('expire_date', '>=', $currentDate)
                ->whereIn('products.status', ['published', 'processing'])
                ->whereHas('nutrient', fn($q) => $q->whereNotNull('calories'))
                ->get()
                ->sortBy(fn($p) => abs(($p->nutrient->calories ?? 0) - $mealTarget))
                ->values();

            $pools[$mealTypeName] = [
                'target'       => $mealTarget,
                'meal_type_id' => $mealType?->id,
                'products'     => $products,
            ];
        }

        // Build next 7 days (starting tomorrow). Rotate products for daily variety.
        $weekly = [];
        for ($i = 1; $i <= 7; $i++) {
            $date  = Carbon::now()->addDays($i);
            $meals = [];

            foreach ($split as $mealTypeName => $ratio) {
                $pool    = $pools[$mealTypeName];
                $product = null;

                if ($pool['products']->count() > 0) {
                    $p = $pool['products'][($i - 1) % $pool['products']->count()];
                    $product = [
                        'id'            => $p->id,
                        'name'          => $p->name,
                        'image'         => $p->image,
                        'price'         => $p->price,
                        'calories'      => $p->nutrient->calories ?? 0,
                        'calories_unit' => $p->nutrient->calories_unit ?? 'kcal',
                        'client_name'   => $p->client ? trim($p->client->firstName . ' ' . $p->client->lastName) : null,
                    ];
                }

                $meals[] = [
                    'meal_type'      => $mealTypeName,
                    'meal_type_id'   => $pool['meal_type_id'],
                    'calorie_target' => $pool['target'],
                    'product'        => $product,
                ];
            }

            $weekly[] = [
                'date'      => $date->format('Y-m-d'),
                'day_label' => $date->format('D, d M Y'),
                'meals'     => $meals,
            ];
        }

        return $weekly;
    }

    /**
     * Build the suggestion payload from past order history only
     * (no profile → no BMI / no calorie target).
     */
    private function buildHistorySuggestionResponse($customerId, string $period = 'last_week'): array
    {
        $start = $period === 'last_month'
            ? Carbon::now()->subMonth()->startOfDay()
            : Carbon::now()->subWeek()->startOfDay();
        $end = Carbon::now()->endOfDay();

        $pastItems = MealOrderItem::with([
                'product.nutrient',
                'product.client:id,firstName,lastName',
                'mealType',
            ])
            ->whereHas('mealOrder', fn($q) =>
                $q->where('customer_id', $customerId)
                  ->whereBetween('created_at', [$start, $end]))
            ->get();

        $hasHistory = $pastItems->isNotEmpty();

        return [
            'period'        => $period,
            'conditions'    => [],
            'analysis'      => $this->analysePastOrders($pastItems),
            'weekly_plan'   => $hasHistory ? $this->buildWeeklyPlanFromHistory($pastItems) : [],
            'has_history'   => $hasHistory,
            'profile_based' => false,
        ];
    }

    /**
     * Build a 7-day plan purely from the customer's previously ordered products,
     * grouped by meal type and rotated for daily variety.
     */
    private function buildWeeklyPlanFromHistory($pastItems): array
    {
        $mealOrder = ['Breakfast', 'Lunch', 'Snacks', 'Dinner'];

        // Group distinct products per meal type, tallying order frequency
        $byMealType = [];
        foreach ($pastItems as $item) {
            if (!$item->product) continue;
            $mtName = $item->mealType->name ?? 'Other';
            $pid    = $item->product->id;
            if (!isset($byMealType[$mtName][$pid])) {
                $byMealType[$mtName][$pid] = [
                    'product'      => $item->product,
                    'meal_type_id' => $item->meal_type_id,
                    'count'        => 0,
                ];
            }
            $byMealType[$mtName][$pid]['count'] += $item->quantity;
        }

        // Map each meal type's products (most-ordered first) to the card shape
        $pools = [];
        foreach ($mealOrder as $mtName) {
            $entries    = collect($byMealType[$mtName] ?? [])->sortByDesc('count')->values();
            $mealTypeId = $entries->isNotEmpty()
                ? $entries->first()['meal_type_id']
                : optional(MealType::whereRaw('LOWER(name) = ?', [strtolower($mtName)])->first())->id;

            $products = $entries->map(function ($entry) {
                $p = $entry['product'];
                return [
                    'id'            => $p->id,
                    'name'          => $p->name,
                    'image'         => $p->image,
                    'price'         => $p->price,
                    'calories'      => $p->nutrient->calories ?? 0,
                    'calories_unit' => $p->nutrient->calories_unit ?? 'kcal',
                    'client_name'   => $p->client ? trim($p->client->firstName . ' ' . $p->client->lastName) : null,
                ];
            })->values()->all();

            $pools[$mtName] = ['meal_type_id' => $mealTypeId, 'products' => $products];
        }

        // Build the next 7 days, rotating through each meal type's product list
        $weekly = [];
        for ($i = 1; $i <= 7; $i++) {
            $date  = Carbon::now()->addDays($i);
            $meals = [];

            foreach ($mealOrder as $mtName) {
                $pool    = $pools[$mtName];
                $product = count($pool['products']) > 0
                    ? $pool['products'][($i - 1) % count($pool['products'])]
                    : null;

                $meals[] = [
                    'meal_type'      => $mtName,
                    'meal_type_id'   => $pool['meal_type_id'],
                    'calorie_target' => $product['calories'] ?? 0,
                    'product'        => $product,
                ];
            }

            $weekly[] = [
                'date'      => $date->format('Y-m-d'),
                'day_label' => $date->format('D, d M Y'),
                'meals'     => $meals,
            ];
        }

        return $weekly;
    }
}