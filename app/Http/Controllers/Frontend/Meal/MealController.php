<?php
namespace App\Http\Controllers\Frontend\Meal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cookie;
use App\Helpers\JWTToken;
use App\Models\MealKeyword;
use App\Models\Product;
use Exception;
use Carbon\Carbon;

class MealController extends Controller
{
    public function favouriteMealPage()
    {
        return view('frontend.pages.favourite-meal.index');
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
            $token = $request->cookie('token');
            $customerId = null;
            if ($token) {
                $result = JWTToken::VerifyToken($token);
                if ($result !== "unauthorized") {
                    $customerId = $result->userID;
                }
            }

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
        return view('frontend.pages.favourite-meal.meal-details');
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

}
