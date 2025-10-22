Below is ValidationHelper:

<?php

namespace App\Helpers;

use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class ValidationHelper
{
    public static function aboutValidationRules($isUpdate = false)
    {
        $rules = [
            'title' => 'required|string|min:3|max:100',
            'description' => 'required|string|min:10',
            'donator' => 'required|string|min:10',
            'donatee' => 'required|string|min:10',
            'image' => $isUpdate ? 'sometimes|image|mimes:jpeg,png,jpg,webp|max:2048' : 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ];

        return $rules;
    }
}


Below is ItemHelper:

<?php

namespace App\Helpers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Models\About;
use App\Models\SiteSetting;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Hero;
use App\Models\User;
use App\Models\Coupon;
use App\Models\DeliveryCharge;

class ItemHelper
{
    public static function prepareAboutData($request, $imagePath = null)
    {
        return [
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'donator' => $request->input('donator'),
            'donatee' => $request->input('donatee'),
            'image' => $imagePath,
        ];
    }

    public static function storeOrUpdateAbout($data, $about = null)
    {
        if ($about) {
            $about->update($data);
        } else {
            $about = About::create($data);
        }
        return $about;
    }
}

<?php
namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use App\Models\CustomerMenu;
use App\Helpers\ValidationHelper;
use App\Helpers\ItemHelper;
use Exception;

class CustomerMenuController extends Controller
{
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $request->validate(ValidationHelper::customerMenuValidationRules());

            $customerMenuData = ItemHelper::prepareCustomerMenuData($request);
            $customerMenu = ItemHelper::storeOrUpdateCustomerMenu($customerMenuData);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Menu created successfully.',
                'data' => $customerMenu,
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
                'message' => 'Menu creation failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request)
    {
        DB::beginTransaction();

        try {
            $id = $request->input('id');
            $request->validate(ValidationHelper::customerMenuValidationRules(true));

            $customerMenu = CustomerMenu::findOrFail($id);
            $customerMenuData = ItemHelper::prepareCustomerMenuData($request);
            $updatedCustomerMenu = ItemHelper::storeOrUpdateCustomerMenu($customerMenuData, $customerMenu);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Menu updated successfully.',
                'data' => $updatedCustomerMenu,
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
                'message' => 'Menu update failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

Give me ItemHelper for below CustomerMenu model:

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerMenu extends Model
{
    protected $table = 'customer_menus';

    protected $fillable = ['customer_id','meal_type_id','name','description'];
}