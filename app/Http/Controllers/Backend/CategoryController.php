<?php
namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\ValidationHelper;
use App\Helpers\ImageHelper;
use App\Helpers\ItemHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException; 
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use App\Models\Category;

class CategoryController extends Controller
{
    public function CategoryPage()
    {
        return view('backend.pages.category.index');
    }


    public function index(Request $request)
    {
        try {
            $category = Category::latest()->get();

            return response()->json([
                'status' => 'success',
                'data' => $category
            ], 200); 

        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while retrieving data',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function create()
    {
        return view('backend.pages.category.create');
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate(ValidationHelper::categoryValidationRules());

            $imagePath = $request->hasFile('image')
                ? ImageHelper::processAndSaveImage($request->file('image'), 'category')
                : null;

            $categoryData = ItemHelper::prepareCategoryData($request, $imagePath);
            $category = ItemHelper::storeOrUpdateCategory($categoryData);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Category information created successfully.',
                'data' => $category,
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
                'message' => 'Category creation failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function show($id)
    {
        try {
            $category = Category::find($id);

            if (!$category) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Category info not found'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $category
            ], 200);
            
        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function EditPage()
    {
        return view('backend.pages.category.edit');
    }


    public function update(Request $request)
    {
        DB::beginTransaction();
        try {
            $id = $request->input('id');
            $request->validate(ValidationHelper::categoryValidationRules(true));

            $category = ItemHelper::findCategoryById($id);

            $imagePath = $request->hasFile('image')
                ? ImageHelper::processAndSaveImage($request->file('image'), 'category', false, $category->image)
                : $category->image;

            $categoryData = ItemHelper::prepareCategoryData($request, $imagePath);
            $updatedCategory = ItemHelper::storeOrUpdateCategory($categoryData, $category);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Category updated successfully.',
                'data' => $updatedCategory,
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
                'message' => 'Category update failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function delete(Request $request)
    {
        try {
            $cat_id = $request->input('id');
            $category = Category::findOrFail($cat_id);

            if(!empty($category->image)){
                ImageHelper::deleteOldImages($category->image, 'category');
            }

            $category->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Category deleted successfully.'
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Category information not found',
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