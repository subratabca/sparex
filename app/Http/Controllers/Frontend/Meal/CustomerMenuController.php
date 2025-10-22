<?php
namespace App\Http\Controllers\Frontend\Meal;

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
    public function index()
    {
        return view('frontend.pages.customer-menu.index');
    }

    public function getList()
    {
        try {
            $customerMenus = CustomerMenu::with('mealType')
                                ->orderBy('id', 'desc')
                                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $customerMenus
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
        return view('frontend.pages.customer-menu.create');
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $request->validate(ValidationHelper::customerMenuValidationRules(false, true));

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

    public function show($id)
    {
        try {
            $customerMenu = CustomerMenu::find($id);

            if (!$customerMenu) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Menu not found'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $customerMenu
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while fetching menu data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function edit($id)
    {
        return view('frontend.pages.customer-menu.edit');
    }

    public function update(Request $request)
    {
        DB::beginTransaction();

        try {
            $id = $request->input('id');
            $request->validate(ValidationHelper::customerMenuValidationRules(true, true, $id));

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

    public function delete(Request $request)
    {
        try {
            $customerMenu = CustomerMenu::findOrFail($request->id);
            $customerMenu->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Meal type deleted successfully.',
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Menu not found.',
                'error' => $e->getMessage(),
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Menu deletion failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
