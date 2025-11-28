<?php
namespace App\Http\Controllers\Frontend\Meal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Validation\ValidationException;
use App\Helpers\JWTToken;
use Exception;
use App\Models\Product;
use Carbon\Carbon;

class MealPlanController extends Controller
{
    public function index()
    {
        return view('frontend.pages.meal-plan.index');
    }

    public function getMealsByFood(Request $request)
    {
        try {
            $request->validate([
                'latitude' => 'nullable|numeric|between:-90,90',
                'longitude' => 'nullable|numeric|between:-180,180',
                'page' => 'nullable|integer|min:1'
            ]);

            $currentDate = Carbon::now(new \DateTimeZone('Asia/Dhaka'));
            $latitude = $request->input('latitude');
            $longitude = $request->input('longitude');
            $radius = 10;

            $baseQuery = Product::with(['city', 'client', 'mealTypes', 'category'])
                ->whereHas('category', function ($query) {
                    $query->where('name', 'Food');
                })
                ->whereHas('mealTypes') // 🔥 ensures product has at least one meal type
                ->where('expire_date', '>=', $currentDate)
                ->where(function ($query) {
                    $query->where('status', 'published')
                          ->orWhere('status', 'processing');
                });

            // ✅ Location-based sorting if coordinates are provided
            if ($latitude && $longitude) {
                $locationProducts = (clone $baseQuery)
                    ->selectRaw("*, (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance", [$latitude, $longitude, $latitude])
                    ->having("distance", "<=", $radius)
                    ->orderBy('distance', 'asc')
                    ->get();

                $remainingProducts = (clone $baseQuery)
                    ->whereNotIn('id', $locationProducts->pluck('id'))
                    ->latest()
                    ->get();

                $allProducts = $locationProducts->merge($remainingProducts);
            } else {
                $allProducts = $baseQuery->latest()->get();
            }

            // ✅ Transform response (include mealTypes + client info)
            $allProducts->transform(function ($product) {
                $product->meal_types = $product->mealTypes->map(fn($mealType) => [
                    'id' => $mealType->id,
                    'name' => $mealType->name,
                ]);

                $product->client_info = [
                    'id' => $product->client->id,
                    'first_name' => $product->client->firstName,
                    'last_name' => $product->client->lastName,
                ];

                unset($product->mealTypes, $product->client);

                return $product;
            });

            // ✅ Manual pagination
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
                'message' => 'Request Successful',
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

    public function searchMeal(Request $request)
    {
        try {
            // ✅ Decode token (optional)
            $token = $request->cookie('token');
            $customerId = null;
            if ($token) {
                $result = JWTToken::VerifyToken($token);
                if ($result !== "unauthorized") {
                    $customerId = $result->userID;
                }
            }

            // ✅ Validate input
            $request->validate([
                'query' => 'nullable|string|max:255',
                'meal_type_id' => 'nullable|integer|exists:meal_types,id',
                'page' => 'nullable|integer|min:1',
                'latitude' => 'nullable|numeric|between:-90,90',
                'longitude' => 'nullable|numeric|between:-180,180',
            ]);

            $query = $request->input('query');
            $mealTypeId = $request->input('meal_type_id');
            $latitude = $request->input('latitude');
            $longitude = $request->input('longitude');
            $radius = 10;
            $currentDate = Carbon::now(new \DateTimeZone('Asia/Dhaka'));

            // ✅ Base query: only "Food" category
            $baseQuery = Product::with(['city', 'client', 'mealTypes', 'category'])
                ->whereHas('category', fn($q) => $q->where('name', 'Food'))
                ->where('expire_date', '>=', $currentDate)
                ->where(fn($q) => $q->where('status', 'published')
                                  ->orWhere('status', 'processing'));

            // ✅ Filter by product name or meal type
            if ($query || $mealTypeId) {
                $baseQuery->where(function($q) use ($query, $mealTypeId) {
                    if ($query) {
                        $q->where('name', 'like', "%{$query}%")
                          ->orWhereHas('mealTypes', fn($mt) => $mt->where('name', 'like', "%{$query}%"));
                    }
                    if ($mealTypeId) {
                        $q->whereHas('mealTypes', fn($mt) => $mt->where('id', $mealTypeId));
                    }
                });
            }

            // ✅ Optional location sorting
            if ($latitude && $longitude) {
                $locationProducts = (clone $baseQuery)
                    ->selectRaw("*, (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance", [$latitude, $longitude, $latitude])
                    ->having("distance", "<=", $radius)
                    ->orderBy('distance', 'asc')
                    ->get();

                $remainingProducts = (clone $baseQuery)
                    ->whereNotIn('id', $locationProducts->pluck('id'))
                    ->latest()
                    ->get();

                $allProducts = $locationProducts->merge($remainingProducts);
            } else {
                $allProducts = $baseQuery->latest()->get();
            }

            // ✅ Transform data
            $allProducts->transform(function($product) {
                $product->meal_types = $product->mealTypes->map(fn($mt) => [
                    'id' => $mt->id,
                    'name' => $mt->name,
                ]);

                $product->client_info = [
                    'id' => $product->client->id,
                    'first_name' => $product->client->firstName,
                    'last_name' => $product->client->lastName,
                ];

                unset($product->mealTypes, $product->client);
                return $product;
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

    public function searchMealByKeyword(Request $request)
    {
        try {
            // ✅ Decode token for user info (optional)
            $token = $request->cookie('token');
            $isValidToken = false;
            $customerId = null;

            if ($token) {
                $result = JWTToken::VerifyToken($token);
                if ($result !== "unauthorized") {
                    $isValidToken = true;
                    $customerId = $result->userID;
                }
            }

            // ✅ Validate input
            $request->validate([
                'query' => 'nullable|string|max:255',
                'page' => 'nullable|integer|min:1',
                'latitude' => 'nullable|numeric|between:-90,90',
                'longitude' => 'nullable|numeric|between:-180,180',
            ]);

            $query = $request->input('query');
            $latitude = $request->input('latitude');
            $longitude = $request->input('longitude');
            $radius = 10;
            $currentDate = Carbon::now(new \DateTimeZone('Asia/Dhaka'));

            // ✅ Base query — only Food category
            $baseQuery = Product::with(['city', 'client', 'mealTypes', 'category'])
                ->whereHas('category', function ($q) {
                    $q->where('name', 'Food');
                })
                ->whereHas('mealTypes') // ensure product has at least one meal type
                ->where('expire_date', '>=', $currentDate)
                ->where(function ($q) {
                    $q->where('status', 'published')
                      ->orWhere('status', 'processing');
                });

            // ✅ Search by product name OR related meal type name
            if (!empty($query)) {
                $baseQuery->where(function ($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                      ->orWhereHas('mealTypes', function ($mealTypeQuery) use ($query) {
                          $mealTypeQuery->where('name', 'like', "%{$query}%");
                      });
                });
            }

            // ✅ Optional location-based sorting
            if ($latitude && $longitude) {
                $locationProducts = (clone $baseQuery)
                    ->selectRaw("*, (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance", [$latitude, $longitude, $latitude])
                    ->having("distance", "<=", $radius)
                    ->orderBy('distance', 'asc')
                    ->get();

                $remainingProducts = (clone $baseQuery)
                    ->whereNotIn('id', $locationProducts->pluck('id'))
                    ->latest()
                    ->get();

                $allProducts = $locationProducts->merge($remainingProducts);
            } else {
                $allProducts = $baseQuery->latest()->get();
            }

            // ✅ Transform data for frontend
            $allProducts->transform(function ($product) {
                $product->meal_types = $product->mealTypes->map(fn($mealType) => [
                    'id' => $mealType->id,
                    'name' => $mealType->name,
                ]);

                $product->client_info = [
                    'id' => $product->client->id,
                    'first_name' => $product->client->firstName,
                    'last_name' => $product->client->lastName,
                ];

                unset($product->mealTypes, $product->client);
                return $product;
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

    public function searchMealByType(Request $request)
    {
        try {
            $mealTypeId = $request->input('meal_type_id');
            $page = $request->input('page', 1);
            $latitude = $request->input('latitude');
            $longitude = $request->input('longitude');
            $radius = 10;
            $currentDate = Carbon::now(new \DateTimeZone('Asia/Dhaka'));

            $baseQuery = Product::with(['city', 'client', 'mealTypes', 'category'])
                ->whereHas('category', fn($q) => $q->where('name', 'Food'))
                ->where('expire_date', '>=', $currentDate)
                ->where(fn($q) => $q->where('status', 'published')->orWhere('status', 'processing'));

            // ✅ Only filter by meal type if a meal_type_id is provided
            if ($mealTypeId) {
                $baseQuery->whereHas('mealTypes', fn($q) => $q->where('id', $mealTypeId));
            }

            // Optional: location sorting
            if ($latitude && $longitude) {
                $locationProducts = (clone $baseQuery)
                    ->selectRaw("*, (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance", [$latitude, $longitude, $latitude])
                    ->having("distance", "<=", $radius)
                    ->orderBy('distance', 'asc')
                    ->get();

                $remainingProducts = (clone $baseQuery)
                    ->whereNotIn('id', $locationProducts->pluck('id'))
                    ->latest()
                    ->get();

                $allProducts = $locationProducts->merge($remainingProducts);
            } else {
                $allProducts = $baseQuery->latest()->get();
            }

            // Transform for frontend
            $allProducts->transform(fn($product) => [
                'id' => $product->id,
                'name' => $product->name,
                'meal_types' => $product->mealTypes->map(fn($mt) => ['id' => $mt->id, 'name' => $mt->name]),
                'client_info' => $product->client ? [
                    'id' => $product->client->id,
                    'first_name' => $product->client->firstName,
                    'last_name' => $product->client->lastName,
                ] : null,
            ]);

            // Pagination
            $perPage = 6;
            $paginated = new LengthAwarePaginator(
                $allProducts->forPage($page, $perPage)->values(),
                $allProducts->count(),
                $perPage,
                $page,
                ['path' => LengthAwarePaginator::resolveCurrentPath()]
            );

            return response()->json([
                'status' => 'success',
                'products' => $paginated->toArray(),
                'total' => $allProducts->count()
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function searchMealByType1111(Request $request)
    {
        try {
            $mealTypeId = $request->input('meal_type_id');
            $page = $request->input('page', 1);
            $latitude = $request->input('latitude');
            $longitude = $request->input('longitude');
            $radius = 10;
            $currentDate = Carbon::now(new \DateTimeZone('Asia/Dhaka'));

            $baseQuery = Product::with(['city', 'client', 'mealTypes', 'category'])
                ->whereHas('category', fn($q) => $q->where('name', 'Food'))
                ->whereHas('mealTypes', fn($q) => $mealTypeId ? $q->where('id', $mealTypeId) : true)
                ->where('expire_date', '>=', $currentDate)
                ->where(fn($q) => $q->where('status', 'published')->orWhere('status', 'processing'));

            // Optional: location sorting
            if ($latitude && $longitude) {
                $locationProducts = (clone $baseQuery)
                    ->selectRaw("*, (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance", [$latitude, $longitude, $latitude])
                    ->having("distance", "<=", $radius)
                    ->orderBy('distance', 'asc')
                    ->get();

                $remainingProducts = (clone $baseQuery)
                    ->whereNotIn('id', $locationProducts->pluck('id'))
                    ->latest()
                    ->get();

                $allProducts = $locationProducts->merge($remainingProducts);
            } else {
                $allProducts = $baseQuery->latest()->get();
            }

            // Transform for frontend
            $allProducts->transform(fn($product) => [
                'id' => $product->id,
                'name' => $product->name,
                'meal_types' => $product->mealTypes->map(fn($mt) => ['id' => $mt->id, 'name' => $mt->name]),
                'client_info' => [
                    'id' => $product->client->id,
                    'first_name' => $product->client->firstName,
                    'last_name' => $product->client->lastName,
                ]
            ]);

            // Pagination
            $perPage = 6;
            $paginated = new LengthAwarePaginator(
                $allProducts->forPage($page, $perPage)->values(),
                $allProducts->count(),
                $perPage,
                $page,
                ['path' => LengthAwarePaginator::resolveCurrentPath()]
            );

            return response()->json([
                'status' => 'success',
                'products' => $paginated->toArray(),
                'total' => $allProducts->count()
            ], 200);

        } catch (Exception $e) {
            return response()->json(['status' => 'failed', 'error' => $e->getMessage()], 500);
        }
    }


    public function getMealTypeByProduct($product_id)
    {
        try {
            $product = Product::find($product_id);

            if (!$product) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Product not found',
                ], 404);
            }

            $mealTypes = $product->mealTypes()
                ->select('meal_types.id', 'meal_types.name')
                ->get();

            return response()->json([
                'status' => 'success',
                'meal_types' => $mealTypes,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

}
