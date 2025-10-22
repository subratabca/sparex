<?php
namespace App\Http\Controllers\Frontend\Meal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use App\Models\CustomerMenu;
use App\Models\MealOrder;
use App\Models\MealOrderItem;
use App\Helpers\ValidationHelper;
use App\Helpers\ItemHelper;
use Exception;

class MealOrderController extends Controller
{
    public function index()
    {
        return view('frontend.pages.meal-order.index');
    }

    public function getList()
    {
        try {
            // Get all meal orders with customer and meal items
            $mealOrders = MealOrder::with(['customer', 'items.mealType'])
                ->orderBy('order_date', 'desc')
                ->get();

            // Format the data
            $data = $mealOrders->map(function ($order) {
                $mealTypes = $order->items->pluck('mealType.name')->unique()->implode(', ');

                return [
                    'id' => $order->id,
                    'customer_name' => trim(($order->customer->firstName ?? '') . ' ' . ($order->customer->lastName ?? '')) ?: '-',
                    'order_date' => $order->order_date,
                    'meal_types' => $mealTypes ?: '-',
                ];
            });

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

    public function groupedByMealType(Request $request)
    {
        try {
            $menus = CustomerMenu::with('mealType')
                ->orderBy('meal_type_id')
                ->get();

            // Group menus by meal type
            $groupedMenus = [];
            foreach ($menus as $menu) {
                $groupedMenus[$menu->meal_type_id][] = [
                    'id' => $menu->id,
                    'name' => $menu->name,
                    'description' => $menu->description,
                ];
            }

            return response()->json([
                'status' => 'success',
                'data' => $groupedMenus
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
        return view('frontend.pages.meal-order.create');
    }

    public function store(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.meal_type_id' => 'required|integer|exists:meal_types,id',
            'items.*.menu_id' => 'required|integer|exists:customer_menus,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'nullable|numeric|min:0',
            'items.*.total_price' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $customerId = $request->header('id'); 
            $orderDate = $request->date;

            // Check if the customer already has an order for this date
            $mealOrder = MealOrder::firstOrCreate(
                ['customer_id' => $customerId, 'order_date' => $orderDate],
                ['status' => 'pending']
            );

            // Clear existing items if customer is updating the order
            //$mealOrder->items()->delete();

            // Prepare items data
            $itemsData = collect($request->items)->map(function ($item) use ($mealOrder) {
                $quantity = $item['quantity'] ?? 1;
                $unitPrice = $item['unit_price'] ?? 0;
                $totalPrice = $item['total_price'] ?? $quantity * $unitPrice;

                return [
                    'meal_order_id' => $mealOrder->id,
                    'meal_type_id' => $item['meal_type_id'],
                    'menu_id' => $item['menu_id'],
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                    'status' => 'pending',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->toArray();

            // Insert all items at once
            MealOrderItem::insert($itemsData);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Meal order placed successfully.',
                'data' => $mealOrder->load('items')
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
                'message' => 'Meal order creation failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $order = MealOrder::with(['items', 'items.menu', 'items.mealType', 'customer'])
                ->findOrFail($id);

            $orderData = [
                'id' => $order->id,
                'customer_name' => trim(($order->customer->firstName ?? '') . ' ' . ($order->customer->lastName ?? '')) ?: '-',
                'order_date' => $order->order_date,
                'items' => $order->items->map(function ($item) {
                    return [
                        'meal_type_id' => $item->meal_type_id,
                        'menu_id' => $item->menu_id,
                    ];
                }),
            ];

            return response()->json([
                'status' => 'success',
                'data' => $orderData
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function edit($id)
    {
        return view('frontend.pages.meal-order.edit');
    }

    public function update(Request $request)
    {
        DB::beginTransaction();

        try {
            // Basic validation
            $id = $request->input('id');
            $validated = $request->validate([
                'date' => 'required|date',
                'items' => 'required|array|min:1',
                'items.*.meal_type_id' => 'required|integer|exists:meal_types,id',
                'items.*.menu_id' => 'required|integer|exists:customer_menus,id',
                'items.*.quantity' => 'nullable|integer|min:1',
                'items.*.unit_price' => 'nullable|numeric|min:0',
                'items.*.total_price' => 'nullable|numeric|min:0',
            ]);

            // Find existing order
            $order = MealOrder::findOrFail($id);

            // Update order info
            $order->order_date = $validated['date'];
            $order->save();

            // Delete old items before inserting updated ones
            MealOrderItem::where('meal_order_id', $order->id)->delete();

            // Reinsert all new order items
            foreach ($validated['items'] as $item) {
                MealOrderItem::create([
                    'meal_order_id' => $order->id,
                    'meal_type_id' => $item['meal_type_id'],
                    'menu_id' => $item['menu_id'],
                    'quantity' => $item['quantity'] ?? 1,
                    'unit_price' => $item['unit_price'] ?? 0,
                    'total_price' => $item['total_price'] ?? 0,
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Meal order updated successfully.',
                'data' => [
                    'order_id' => $order->id,
                    'order_date' => $order->order_date,
                    'items_count' => count($validated['items']),
                ]
            ], 200);

        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'failed',
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);

        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'failed',
                'message' => 'Meal order not found.',
            ], 404);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'failed',
                'message' => 'Meal order update failed.',
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
