<?php
namespace App\Http\Controllers\Frontend\Meal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use App\Models\MealType;
use App\Helpers\ValidationHelper;
use App\Helpers\ItemHelper;
use Exception;

class MealTypeController extends Controller
{
    public function index()
    {
        return view('frontend.pages.meal-type.index');
    }

    public function getList()
    {
        try {
            $mealTypes = MealType::orderBy('id', 'desc')->get();

            return response()->json([
                'status' => 'success',
                'data' => $mealTypes
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
        return view('frontend.pages.meal-type.create');
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $request->validate(ValidationHelper::mealTypeValidationRules());

            $mealTypeData = ItemHelper::prepareMealTypeData($request);
            $mealType = ItemHelper::storeOrUpdateMealType($mealTypeData);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Meal type created successfully.',
                'data' => $mealType,
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
                'message' => 'Meal type creation failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $mealType = MealType::find($id);

            if (!$mealType) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Meal type not found'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $mealType
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while fetching meal type data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function edit($id)
    {
        return view('frontend.pages.meal-type.edit');
    }

    public function update(Request $request)
    {
        DB::beginTransaction();

        try {
            $id = $request->input('id');
            $request->validate(ValidationHelper::mealTypeValidationRules(true));

            $mealType = MealType::findOrFail($id);
            $mealTypeData = ItemHelper::prepareMealTypeData($request);
            $updatedMealType = ItemHelper::storeOrUpdateMealType($mealTypeData, $mealType);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Meal type updated successfully.',
                'data' => $updatedMealType,
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
                'message' => 'Meal type update failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function delete(Request $request)
    {
        try {
            $mealType = MealType::findOrFail($request->id);
            $mealType->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Meal type deleted successfully.',
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Meal type not found.',
                'error' => $e->getMessage(),
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Meal type deletion failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
