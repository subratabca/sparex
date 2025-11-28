<?php
namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\MealOrder;
use App\Models\MealOrderItem;
use Exception;


class AdminMealOrderController extends Controller
{
    public function index()
    {
        return view('backend.pages.meal-order.index');
    }

    public function getMealOrders(Request $request)
    {
        try {
            $mealOrders = MealOrder::with([
                    'customer',
                    'items.mealType',
                ])
                ->latest()
                ->get();

            $data = $mealOrders->map(function ($order) {
                $mealTypes = $order->items
                    ->pluck('mealType.name')
                    ->unique()
                    ->implode(', ');

                $subtotal = $order->clientMealOrders->sum('subtotal');
                $tax = $order->clientMealOrders->sum('tax');
                $payable = $order->clientMealOrders->sum('payable_amount');

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
        return view('backend.pages.meal-order.view');
    }

    public function getMealOrderDetails(Request $request, $meal_order_id)
    {
        try {
            $order = MealOrder::with([
                'customer',
                'items.mealType',
                'items.product.client',
                'clientMealOrders.client'
            ])->find($meal_order_id);

            if (!$order || $order->items->isEmpty()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Meal order not found or has no items.',
                ], 404);
            }

            $itemsGrouped = $order->items->groupBy('meal_type_id');

            $meals = [];
            foreach ($itemsGrouped as $meal_type_id => $items) {
                $mealTypeName = $items->first()->mealType->name ?? 'N/A';

                $products = $items->map(function($item) {
                $client = $item->product->client ?? null;
                $clientName = $client 
                    ? trim($client->firstName . ' ' . ($client->lastName ?? ''))
                    : 'N/A';

                    return [
                        'name' => $item->product->name ?? 'N/A',
                        'image' => $item->product->image ? asset('upload/product/medium/' . $item->product->image) : asset('upload/no_image.jpg'),
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'total_price' => $item->total_price,
                        'client_name' => $clientName, 
                    ];
                });

                $meals[] = [
                    'meal_type_name' => $mealTypeName,
                    'products' => $products,
                ];
            }

            $subtotal = $order->clientMealOrders->sum('subtotal');
            $tax = $order->clientMealOrders->sum('tax');
            $payable_amount = $order->clientMealOrders->sum('payable_amount');

            return response()->json([
                'status' => 'success',
                'data' => [
                    'meal_date' => $order->meal_date,
                    'customer_name' => trim(($order->customer->firstName ?? '') . ' ' . ($order->customer->lastName ?? '')),
                    'subtotal' => $subtotal,
                    'tax' => $tax,
                    'payable_amount' => $payable_amount,
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