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
use App\Models\DeliveryCharge;
use Illuminate\Support\Facades\Validator;

class ClientDeliveryChargeController extends Controller
{
    public function deliveryChargePage()
    {
        return view('client.pages.delivery-charge.index');
    }

    public function index(Request $request)
    {
        try {
            $client_id = $request->header('id');
            $deliveryCharge = DeliveryCharge::with('client')
                ->where('client_id', $client_id)
                ->latest()
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $deliveryCharge
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
        return view('client.pages.delivery-charge.create');
    }

    public function store(Request $request)
    {
        try {
            $client_id = $request->header('id');
            $existingCharge = DeliveryCharge::where('client_id', $client_id)->first();
            if ($existingCharge) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'You have already created a delivery charge. You cannot create another.',
                ], 400);
            }

            $validator = ValidationHelper::validateDeliveryCharge($request->all());
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Validation Failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $request->merge(['client_id' => $client_id]);
            $deliveryData = ItemHelper::prepareDeliveryChargeData($request);
            $deliveryCharge = ItemHelper::storeOrUpdateDeliveryCharge($deliveryData);

            return response()->json([
                'status' => 'success',
                'message' => 'Delivery charge created successfully.',
                'data' => $deliveryCharge,
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Delivery charge creation failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $deliveryCharge = DeliveryCharge::find($id);

            if (!$deliveryCharge) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Delivery charge not found'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $deliveryCharge
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
        return view('client.pages.delivery-charge.edit');
    }

    public function update(Request $request)
    {
        try {
            $id = $request->input('id');
            $validator = ValidationHelper::validateDeliveryCharge($request->all());

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Validation Failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $deliveryCharge = DeliveryCharge::findOrFail($id);
            $client_id = $request->header('id');
            $request->merge(['client_id' => $client_id]);

            $deliveryData = ItemHelper::prepareDeliveryChargeData($request);
            $updatedDeliveryCharge = ItemHelper::storeOrUpdateDeliveryCharge($deliveryData, $deliveryCharge);

            return response()->json([
                'status' => 'success',
                'message' => 'Delivery charge updated successfully.',
                'data' => $updatedDeliveryCharge,
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
                'message' => 'Delivery charge update failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function delete(Request $request)
    {
        try {
            $deliveryChargeId = $request->input('id');
            $deliveryCharge = DeliveryCharge::findOrFail($deliveryChargeId);
            $deliveryCharge->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Delivery charge deleted successfully.'
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Delivery charge not found',
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