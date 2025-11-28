<?php
namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\MealOrder;
use App\Models\MealOrderItem;
use Exception;


class ClientMealOrderController extends Controller
{
    public function index()
    {
        return view('client.pages.meal-order.index');
    }

    public function getMealOrders(Request $request)
    {
        try {
            $client_id = $request->header('id');

            // ✅ Fetch meal orders where any item’s product belongs to this client
            $mealOrders = MealOrder::with(['customer', 'items.mealType', 'items.product'])
                ->whereHas('items.product', function ($query) use ($client_id) {
                    $query->where('client_id', $client_id);
                })
                ->latest()
                ->get();

            // ✅ Format data for frontend table
            $data = $mealOrders->map(function ($order) use ($client_id) {
                // Get meal types for this client’s products only
                $mealTypes = $order->items
                    ->filter(function ($item) use ($client_id) {
                        return $item->product && $item->product->client_id == $client_id;
                    })
                    ->pluck('mealType.name')
                    ->unique()
                    ->implode(', ');

                return [
                    'id' => $order->id,
                    'customer_name' => trim(($order->customer->firstName ?? '') . ' ' . ($order->customer->lastName ?? '')) ?: '-',
                    'meal_date' => $order->meal_date,
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

    public function view(Request $request)
    {
        return view('client.pages.meal-order.view');
    }

    public function getMealOrderDetails(Request $request, $meal_order_id)
    {
        try {
            $client_id = $request->header('id'); 

            $order = MealOrder::with([
                'customer', 
                'items' => function($query) use ($client_id) {
                    $query->where('client_id', $client_id)
                          ->with(['mealType', 'product']);
                }
            ])->find($meal_order_id);

            if (!$order || $order->items->isEmpty()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Meal order not found or no items for this client.',
                ], 404);
            }

            // Fetch client-specific totals
            $clientOrder = $order->clientMealOrders()->where('client_id', $client_id)->first();

            $meals = [];
            $itemsGrouped = $order->items->groupBy('meal_type_id');

            foreach ($itemsGrouped as $meal_type_id => $items) {
                $mealTypeName = $items->first()->mealType->name ?? 'N/A';

                $products = $items->map(function($item) {
                    return [
                        'name' => $item->product->name ?? 'N/A',
                        'image' => $item->product->image ? asset('upload/product/medium/' . $item->product->image) : asset('upload/no_image.jpg'),
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'total_price' => $item->total_price,
                    ];
                });

                $meals[] = [
                    'meal_type_name' => $mealTypeName,
                    'products' => $products,
                ];
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'meal_date' => $order->meal_date,
                    'customer_name' => $order->customer->firstName . ' ' . $order->customer->lastName,
                    'subtotal' => $clientOrder->subtotal ?? 0,
                    'tax' => $clientOrder->tax ?? 0,
                    'payable_amount' => $clientOrder->payable_amount ?? 0,
                    'meals' => $meals,
                ]
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

}