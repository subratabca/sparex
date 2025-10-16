<?php
namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Validation\ValidationException;
use App\Helpers\JWTToken;
use Exception;
use App\Models\SiteSetting;
use App\Models\Product;
use App\Models\Hero;
use Carbon\Carbon;


class HomeController extends Controller
{
    public function HomePage()
    {
        return view('frontend.pages.home-page');
    }

    public function SettingList()
    {
        try {
            $data = SiteSetting::first(); 
            return response()->json([
                'status' => 'success',
                'message' => 'Request Successful',
                'data' => $data
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while processing your request',
                'error' => $e->getMessage() 
            ], 500);
        }
    }

    public function HeroPageInfo()
    {
        try {
            $data = Hero::first();

            if ($data) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Request Successful',
                    'data' => $data
                ], 200);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'No data found',
                    'data' => []
                ], 404);
            }

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while processing your request',
                'error' => $e->getMessage() 
            ], 500);
        }
    }

    public function getProducts(Request $request)
    {
        try {
            $request->validate([
                'latitude' => 'nullable|numeric|between:-90,90',
                'longitude' => 'nullable|numeric|between:-180,180',
                'page' => 'nullable|integer|min:1'
            ]);
            
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

            $currentDate = Carbon::now(new \DateTimeZone('Asia/Dhaka'));
            $latitude = $request->input('latitude');
            $longitude = $request->input('longitude');
            $radius = 10; 

            if ($latitude && $longitude) {
                $locationProducts = Product::with('city', 'client.followers')->selectRaw("*, (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance", [$latitude, $longitude, $latitude])
                    ->having("distance", "<=", $radius)
                    ->where('expire_date', '>=', $currentDate)
                    ->where(function ($query) {
                        $query->where('status', 'published')
                              ->orWhere('status', 'processing');
                    })
                    ->orderBy('distance', 'asc')
                    ->get();

                $remainingProducts = Product::where('expire_date', '>=', $currentDate)
                    ->where(function ($query) {
                        $query->where('status', 'published')
                              ->orWhere('status', 'processing');
                    })
                    ->whereNotIn('id', $locationProducts->pluck('id'))
                    ->latest()
                    ->get();

                $allProducts = $locationProducts->merge($remainingProducts);
            } else {
                $allProducts = Product::with('city', 'client.followers')->where('expire_date', '>=', $currentDate)
                    ->where(function ($query) {
                        $query->where('status', 'published')
                              ->orWhere('status', 'processing');
                    })
                    ->latest()
                    ->get();
            }

            $allProducts->each(function ($product) use ($customerId) {
                $product->isFollowing = 
                    $product->client->followers->firstWhere('customer_id', $customerId)->status ?? 0;
            });

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

    public function searchProduct(Request $request)
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