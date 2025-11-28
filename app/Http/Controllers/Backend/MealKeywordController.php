<?php
namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use App\Models\MealKeyword;
use App\Helpers\ValidationHelper;
use App\Helpers\ItemHelper;
use Exception;

class MealKeywordController extends Controller
{
    public function index()
    {
        return view('backend.pages.meal-keyword.index');
    }

    public function getList()
    {
        try {
            $datas = MealKeyword::with('mealType')->latest()->get();

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

    public function create()
    {
        return view('backend.pages.meal-keyword.create');
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $request->validate(ValidationHelper::mealKeywordValidationRules());

            $mealKeywordData = ItemHelper::prepareMealKeywordData($request);
            $mealKeyword = ItemHelper::storeOrUpdateMealKeyword($mealKeywordData);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Meal keyword created successfully.',
                'data' => $mealKeyword,
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
                'message' => 'Meal keyword creation failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $mealKeyword = MealKeyword::find($id);

            if (!$mealKeyword) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Meal keyword not found'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $mealKeyword
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while fetching meal keyword data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function edit($id)
    {
        return view('backend.pages.meal-keyword.edit');
    }

    public function update(Request $request)
    {
        DB::beginTransaction();

        try {
            $id = $request->input('id');
            $request->validate(ValidationHelper::mealKeywordValidationRules(true));

            $mealKeyword = MealKeyword::findOrFail($id);
            $mealKeywordData = ItemHelper::prepareMealKeywordData($request);
            $updatedMealKeyword = ItemHelper::storeOrUpdateMealKeyword($mealKeywordData, $mealKeyword);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Meal keyword updated successfully.',
                'data' => $updatedMealKeyword,
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
                'message' => 'Meal keyword update failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function delete(Request $request)
    {
        try {
            $mealKeyword = MealKeyword::findOrFail($request->id);
            $mealKeyword->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Meal keyword deleted successfully.',
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Meal keyword not found.',
                'error' => $e->getMessage(),
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Meal keyword deletion failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
