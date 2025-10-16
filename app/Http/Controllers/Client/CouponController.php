<?php
namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\ValidationHelper;
use App\Helpers\ItemHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException; 
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use App\Models\Coupon;

class CouponController extends Controller
{
    public function couponPage()
    {
        return view('client.pages.coupon.index');
    }

    public function index(Request $request)
    {
        try {
            $client_id = $request->header('id');
            $coupons = Coupon::with('client')
                ->where('client_id', $client_id)
                ->latest()
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $coupons
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Failed to retrieve coupons',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function create()
    {
        return view('client.pages.coupon.create');
    }

    public function store(Request $request)
    {
        try {
            $request->validate(ValidationHelper::couponValidationRules());

            $couponData = ItemHelper::prepareCouponData($request);
            $coupon = ItemHelper::storeOrUpdateCoupon($couponData);

            return response()->json([
                'status' => 'success',
                'message' => 'Coupon created successfully.',
                'data' => $coupon,
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Validation Failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Coupon creation failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $coupon = Coupon::find($id);

            if (!$coupon) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Coupon not found'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $coupon
            ], 200);
            
        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function edit()
    {
        return view('client.pages.coupon.edit');
    }

    public function update(Request $request)
    {
        try {
            $id = $request->input('id');
            $request->validate(ValidationHelper::couponValidationRules(true));
            $coupon = Coupon::findOrFail($id);
            $couponData = ItemHelper::prepareCouponData($request);
            $updatedCoupon = ItemHelper::storeOrUpdateCoupon($couponData, $coupon);
            return response()->json([
                'status' => 'success',
                'message' => 'Coupon updated successfully.',
                'data' => $updatedCoupon,
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Validation Failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Coupon update failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function delete(Request $request)
    {
        try {
            $coupon_id = $request->input('id');
            $coupon = Coupon::findOrFail($coupon_id);
            $coupon->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'Coupon deleted successfully.'
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Coupon not found',
                'error' => $e->getMessage()
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Deletion failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}