<?php
namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\ValidationHelper;
use App\Helpers\ImageHelper;
use App\Helpers\ItemHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException; 
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use App\Models\Brand;

class ClientBrandController extends Controller
{
    public function brandPage()
    {
        return view('client.pages.brand.index');
    }

    public function index(Request $request)
    {
        try {
            $brands = Brand::latest()->get();

            return response()->json([
                'status' => 'success',
                'data' => $brands
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
        return view('client.pages.brand.create');
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate(ValidationHelper::brandValidationRules());

            $imagePath = $request->hasFile('image')
                ? ImageHelper::processAndSaveImage($request->file('image'), 'brand')
                : null;

            $brandData = ItemHelper::prepareBrandData($request, $imagePath);
            $brand = ItemHelper::storeOrUpdateBrand($brandData);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Brand information created successfully.',
                'data' => $brand,
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
                'message' => 'Brand creation failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $brand = Brand::find($id);

            if (!$brand) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Brand info not found'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $brand
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
        return view('client.pages.brand.edit');
    }

    public function update(Request $request)
    {
        DB::beginTransaction();
        try {
            $id = $request->input('id');
            $request->validate(ValidationHelper::brandValidationRules(true));

            $brand = ItemHelper::findBrandById($id);

            $imagePath = $request->hasFile('image')
                ? ImageHelper::processAndSaveImage($request->file('image'), 'brand', false, $brand->image)
                : $brand->image;

            $brandData = ItemHelper::prepareBrandData($request, $imagePath);
            $updatedBrand = ItemHelper::storeOrUpdateBrand($brandData, $brand);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Brand updated successfully.',
                'data' => $updatedBrand,
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
                'message' => 'Brand update failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function delete(Request $request)
    {
        try {
            $brand_id = $request->input('id');
            $brand = Brand::findOrFail($brand_id);

            if(!empty($brand->image)){
                ImageHelper::deleteOldImages($brand->image, 'brand');
            }

            $brand->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Brand deleted successfully.'
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Brand information not found',
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