<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Cart;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShippingAddress;
use App\Models\Coupon;
use Exception;

class CheckoutController extends Controller
{
    public function checkoutPage()
    {
        return view('frontend.pages.checkout.checkout-page');
    }

    public function getAuthUserInfo(Request $request)
    {
        try {
            $email = $request->header('email');

            if (!$email) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Unauthorized! Need to login.'
                ], 400);
            }

            $user = User::where('email', $email)->first();
            return response()->json([
                'status' => 'success',
                'message' => 'Request Successful',
                'data' => $user,
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getShippingAddressInfo(Request $request)
    {
        try {
            $email = $request->header('email');

            if (!$email) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Email header is required'
                ], 400);
            }

            $shippingAddresses = ShippingAddress::with(['country', 'county', 'city'])
                ->where('email', $email)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'status' => 'success',
                'message' => 'Shipping addresses retrieved successfully',
                'data' => $shippingAddresses
            ], 200);

        } catch (Exception $e) {
            Log::error('Error fetching shipping addresses: ' . $e->getMessage());
            return response()->json([
                'status' => 'failed',
                'message' => 'Failed to retrieve shipping addresses'
            ], 500);
        }
    }
}
