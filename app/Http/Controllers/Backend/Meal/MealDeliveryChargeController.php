<?php

namespace App\Http\Controllers\Backend\Meal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Helpers\ValidationHelper;
use App\Helpers\ItemHelper;
use App\Models\MealDeliveryCharge;
use Exception;

class MealDeliveryChargeController extends Controller
{
    public function index()
    {
        return view('backend.pages.meal-delivery-charge.index');
    }

    public function getList()
    {
        try {
            $data = MealDeliveryCharge::with(['client', 'mealType'])
                        ->latest()
                        ->get();

            return response()->json([
                'status' => 'success',
                'data' => $data
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function create()
    {
        return view('backend.pages.meal-delivery-charge.create');
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $request->validate(ValidationHelper::mealDeliveryChargeValidationRules());

            $data = ItemHelper::prepareMealDeliveryChargeData($request);
            $record = ItemHelper::storeOrUpdateMealDeliveryCharge($data);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Delivery charge created successfully.',
                'data' => $record,
            ], 201);

        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'failed',
                'message' => 'Validation Failed',
                'errors' => $e->errors(),
            ], 422);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'failed',
                'message' => 'Creation failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function show($id)
    {
        try {
            $record = MealDeliveryCharge::with(['client', 'mealType'])->find($id);

            if (!$record) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Record not found'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $record
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Fetch failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function edit($id)
    {
        return view('backend.pages.meal-delivery-charge.edit');
    }

    public function update(Request $request)
    {
        DB::beginTransaction();

        try {
            if (!$request->id) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Record ID is required for update.',
                ], 400);
            }

            $record = MealDeliveryCharge::findOrFail($request->id);
            $request->validate(
                ValidationHelper::mealDeliveryChargeValidationRules($record->id)
            );

            $data = ItemHelper::prepareMealDeliveryChargeData($request);
            $updated = ItemHelper::storeOrUpdateMealDeliveryCharge($data, $record);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Delivery charge updated successfully.',
                'data' => $updated,
            ], 200);

        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'failed',
                'message' => 'Validation Failed',
                'errors' => $e->errors(),
            ], 422);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'failed',
                'message' => 'Update failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function delete($id)
    {
        try {
            $record = MealDeliveryCharge::findOrFail($id);
            $record->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Delivery charge deleted successfully.',
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Record not found.',
            ], 404);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Deletion failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
