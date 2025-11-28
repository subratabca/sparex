<?php
namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Validation\ValidationException;
use App\Helpers\JWTToken;
use Exception;
use App\Models\Product;
use Carbon\Carbon;

class CustomerMenuController extends Controller
{
    public function index()
    {
        return view('frontend.pages.customer-menu.index');
    }

    public function getMenusByFood111(Request $request)
    {
        try {
            // Fetch products that belong to 'Food' category and have at least one meal type
            $products = Product::with(['city','mealTypes', 'category'])
                ->whereHas('category', function ($query) {
                    $query->whereRaw('LOWER(name) = ?', ['food']);
                })
                ->whereHas('mealTypes') 
                ->where(function ($query) {
                    $query->where('status', 'published')
                          ->orWhere('status', 'processing');
                })
                ->latest()
                ->get();

            // Format meal types for better frontend usage
            $products->transform(function ($product) {
                $product->meal_types = $product->mealTypes->map(function ($mealType) {
                    return [
                        'id' => $mealType->id,
                        'name' => $mealType->name,
                    ];
                });
                unset($product->mealTypes);
                return $product;
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Request Successful',
                'products' => $products,
                'total' => $products->count(),
            ], 200);
        } 
        catch (ValidationException $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } 
        catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

public function getMenusByFood222(Request $request)
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

        // ✅ Base query (only "Food" category + published/processing + not expired)
        $baseQuery = Product::with(['city', 'client', 'mealTypes', 'category'])
            ->whereHas('category', function ($query) {
                $query->where('name', 'Food');
            })
            ->where('expire_date', '>=', $currentDate)
            ->where(function ($query) {
                $query->where('status', 'published')
                      ->orWhere('status', 'processing');
            });

        // ✅ Location-based sorting if coordinates provided
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

        // ✅ Transform response to include meal types + client info
        $allProducts->transform(function ($product) {
            $product->meal_types = $product->mealTypes->map(fn($mealType) => [
                'id' => $mealType->id,
                'name' => $mealType->name,
            ]);

            // include only necessary client info
            $product->client_info = [
                'id' => $product->client->id,
                'first_name' => $product->client->firstName,
                'last_name' => $product->client->lastName,
            ];

            unset($product->mealTypes, $product->client); // cleanup relationships

            return $product;
        });

        // ✅ Paginate manually
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

public function getMenusByFood(Request $request)
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

        // ✅ Base query:
        // - Category must be "Food"
        // - Product must have at least one mealType
        // - Product not expired
        // - Status is published or processing
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



    public function searchMenu(Request $request)
    {
        try {
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

            $query = $request->input('query');
            $currentDate = Carbon::now(new \DateTimeZone('Asia/Dhaka'));

            $products = Product::with(['client', 'client.followers'])
                ->where(function ($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%");
                })
                ->where('expire_date', '>=', $currentDate)
                ->where(function ($q) {
                    $q->where('status', 'published')
                      ->orWhere('status', 'processing');
                })
                ->latest()
                ->get();

            $products->each(function ($product) use ($customerId) {
                $product->isFollowing = 
                    $product->client->followers->firstWhere('customer_id', $customerId)->status ?? 0;
            });


            $page = LengthAwarePaginator::resolveCurrentPage();
            $perPage = 6;
            $paginatedProducts = new LengthAwarePaginator(
                $products->forPage($page, $perPage)->values(),
                $products->count(),
                $perPage,
                $page,
                ['path' => LengthAwarePaginator::resolveCurrentPath()]
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Search Successful',
                'products' => $paginatedProducts->toArray(),
                'total' => $products->count()
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
