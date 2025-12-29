<?php
namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\DeliveryHelper;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\ClientMealOrder;
use App\Models\MealOrder;
use App\Models\MealOrderItem;
use App\Models\MealShippingAddress;
use App\Models\MealDeliveryCharge;
use App\Models\User;
use App\Models\MealType;
use Carbon\Carbon;
use Exception;


class AdminMealPaymentController extends Controller
{
    public function clientPaymentPage()
    {
        return view('backend.pages.meal-payment.payment-list');
    }

	public function getClientPaymentList(Request $request)
	{
	    try {
	        $clientOrders = ClientMealOrder::with([
	            'mealOrder:id,invoice_no,created_at,customer_id',
	            'mealOrder.customer:id,firstName,lastName',
	            'client:id,firstName,lastName',
	            'mealOrder.items:id,meal_order_id,meal_date' // Load meal items to get meal_date
	        ])
	        ->whereHas('mealOrder')
	        ->latest()
	        ->get();

	        // Format data for response
	        $data = $clientOrders->map(function ($item) {
	            $mealOrder = $item->mealOrder;
	            $client = $item->client;
	            
	            // Get customer name from meal order
	            $customerName = '';
	            if ($mealOrder && $mealOrder->customer) {
	                $customerName = trim($mealOrder->customer->firstName . ' ' . ($mealOrder->customer->lastName ?? ''));
	            }

	            // Get the latest meal_date from meal order items
	            $latestMealDate = '-';
	            if ($mealOrder && $mealOrder->items && $mealOrder->items->isNotEmpty()) {
	                // Get the maximum (latest) meal_date
	                $latestMealDate = $mealOrder->items->max('meal_date');
	                
	                // Format the date if it's not null
	                if ($latestMealDate) {
	                    try {
	                        $latestMealDate = Carbon::parse($latestMealDate)->format('d M Y');
	                    } catch (\Exception $e) {
	                        $latestMealDate = '-';
	                    }
	                } else {
	                    $latestMealDate = '-';
	                }
	            }

	            return [
	                'id' => $item->id,
	                'order_id' => $item->meal_order_id,
	                'client_id' => $item->client_id,
	                'order_date' => $mealOrder ? $mealOrder->created_at->format('d M Y') : '-',
	                'invoice_no' => $mealOrder ? $mealOrder->invoice_no : '-',
	                'client_name' => $client ? trim($client->firstName . ' ' . ($client->lastName ?? '')) : '-',
	                'customer_name' => $customerName,
	                'meal_date' => $latestMealDate, // Add latest meal_date
	                'payable_amount' => isset($item->payable_amount) ? '£' . number_format($item->payable_amount, 2) : '£0.00',
	                'payment_status' => $item->payment_status ? ucfirst($item->payment_status) : 'Unknown'
	            ];
	        });

	        return response()->json([
	            'status' => 'success',
	            'data' => $data
	        ], 200);

	    } catch (Exception $e) {
	        return response()->json([
	            'status' => 'failed',
	            'message' => 'An error occurred while retrieving client payments.',
	            'error' => $e->getMessage()
	        ], 500);
	    }
	}

    public function clientPaymentDetailsPage()
    {
        return view('backend.pages.meal-payment.payment-details-by-order');
    }

    public function getClientPaymentDetailsInfo(Request $request, $client_id, $order_id)
    {
        try {
            // First, verify the client exists
            $client = User::where('id', $client_id)
                ->first();
            
            if (!$client) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Client not found.'
                ], 404);
            }

            // Verify the order exists
            $order = MealOrder::find($order_id);
            if (!$order) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Order not found.'
                ], 404);
            }

            // Get the specific client meal order for this client and order
            $clientMealOrder = ClientMealOrder::with([
                'mealOrder:id,order_number,invoice_no,status,created_at,delivery_fee,subtotal,tax,payable_amount,payment_status',
                'client:id,firstName,lastName,email,mobile,image',
            ])
            ->where('client_id', $client_id)
            ->where('meal_order_id', $order_id)
            ->first();

            // Get meal order items for this client and order
            $mealOrderItems = MealOrderItem::with([
                'mealType:id,name',
                'product:id,name,image,price',
                'client:id,firstName,lastName'
            ])
            ->where('client_id', $client_id)
            ->where('meal_order_id', $order_id)
            ->get();

            if ($mealOrderItems->isEmpty()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'No items found for this client in the specified order.'
                ], 404);
            }

            // If no ClientMealOrder exists, create a virtual one from the items
            if (!$clientMealOrder) {
                // Calculate totals from items
                $subtotal = $mealOrderItems->sum('total_price');
                $deliveryFee = 0;
                $tax = 0;
                $payableAmount = $subtotal + $deliveryFee + $tax;

                $clientMealOrder = (object) [
                    'id' => null,
                    'subtotal' => $subtotal,
                    'tax' => $tax,
                    'delivery_fee' => $deliveryFee,
                    'payable_amount' => $payableAmount,
                    'paid_amount' => 0,
                    'payment_status' => 'pending',
                    'created_at' => $order->created_at,
                    'updated_at' => $order->updated_at,
                    'mealOrder' => $order,
                    'client' => $client,
                    'mealOrderItems' => $mealOrderItems
                ];
            } else {
                // Load items for existing ClientMealOrder
                $clientMealOrder->mealOrderItems = $mealOrderItems;
            }

            // Group items by meal_date and meal_type
            $groupedItems = [];
            $totalAmount = 0;
            
            foreach ($clientMealOrder->mealOrderItems as $item) {
                $mealDate = $item->meal_date;
                $mealTypeName = $item->mealType ? $item->mealType->name : 'Other';
                
                if (!isset($groupedItems[$mealDate])) {
                    $groupedItems[$mealDate] = [];
                }
                
                if (!isset($groupedItems[$mealDate][$mealTypeName])) {
                    $groupedItems[$mealDate][$mealTypeName] = [];
                }
                
                $groupedItems[$mealDate][$mealTypeName][] = [
                    'id' => $item->id,
                    'product_name' => $item->product ? $item->product->name : 'N/A',
                    'product_image' => $item->product ? $item->product->image : null,
                    'unit_price' => $item->unit_price,
                    'quantity' => $item->quantity,
                    'total_price' => $item->total_price,
                    'meal_type_id' => $item->meal_type_id,
                    'meal_type_name' => $mealTypeName,
                    'client_name' => $item->client ? $item->client->firstName . ' ' . $item->client->lastName : 'N/A'
                ];
                
                $totalAmount += $item->total_price;
            }

            // Get all meal types for this client's items
            $mealTypeIds = $mealOrderItems->pluck('meal_type_id')->filter()->unique()->values();
            $mealTypes = MealType::whereIn('id', $mealTypeIds)->get(['id', 'name']);

            // Prepare the response data
            $data = [
                'client_meal_order' => [
                    'id' => $clientMealOrder->id,
                    'subtotal' => $clientMealOrder->subtotal,
                    'tax' => $clientMealOrder->tax,
                    'delivery_fee' => $clientMealOrder->delivery_fee,
                    'payable_amount' => $clientMealOrder->payable_amount,
                    'paid_amount' => $clientMealOrder->paid_amount,
                    'payment_status' => $clientMealOrder->payment_status,
                    'created_at' => $clientMealOrder->created_at ? $clientMealOrder->created_at->format('d M Y H:i:s') : null,
                    'updated_at' => $clientMealOrder->updated_at ? $clientMealOrder->updated_at->format('d M Y H:i:s') : null,
                ],
                'order' => [
                    'order_number' => $clientMealOrder->mealOrder->order_number,
                    'invoice_no' => $clientMealOrder->mealOrder->invoice_no,
                    'status' => $clientMealOrder->mealOrder->status,
                    'created_at' => $clientMealOrder->mealOrder->created_at->format('d M Y H:i:s'),
                    'payment_status' => $clientMealOrder->mealOrder->payment_status,
                ],
                'client' => [
                    'id' => $clientMealOrder->client->id,
                    'name' => $clientMealOrder->client->firstName . ' ' . $clientMealOrder->client->lastName,
                    'email' => $clientMealOrder->client->email,
                    'mobile' => $clientMealOrder->client->mobile,
                    'image' => $clientMealOrder->client->image,
                ],
                'items' => $groupedItems,
                'summary' => [
                    'total_items' => $clientMealOrder->mealOrderItems->count(),
                    'total_dates' => count($groupedItems),
                    'total_meal_types' => $mealTypes->count(),
                    'subtotal' => $clientMealOrder->subtotal,
                    'tax' => $clientMealOrder->tax,
                    'delivery_fee' => $clientMealOrder->delivery_fee,
                    'total_amount' => $clientMealOrder->payable_amount,
                    'paid_amount' => $clientMealOrder->paid_amount,
                    'balance_amount' => $clientMealOrder->payable_amount - $clientMealOrder->paid_amount,
                ],
                'meal_types' => $mealTypes,
                'dates' => array_keys($groupedItems),
            ];

            return response()->json([
                'status' => 'success',
                'data' => $data
            ], 200);

        } catch (Exception $e) {
            // Log the error
            error_log('Error in getClientPaymentDetailsInfo: ' . $e->getMessage());
            error_log('Trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while retrieving payment details.',
                'error' => env('APP_DEBUG') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function markClientPaymentAsPaid(Request $request, $client_id, $order_id)
    {
        DB::beginTransaction();

        try {
            // Find the ClientMealOrder record
            $clientMealOrder = ClientMealOrder::where('client_id', $client_id)
                ->where('meal_order_id', $order_id)
                ->first();

            if (!$clientMealOrder) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Client payment record not found.'
                ], 404);
            }

            // Check if already paid
            if ($clientMealOrder->payment_status === 'paid') {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'This client payment is already marked as paid.'
                ], 400);
            }

            // Find the main MealOrder
            $mealOrder = MealOrder::find($order_id);
            if (!$mealOrder) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Main order not found.'
                ], 404);
            }

            // Update ClientMealOrder payment status
            $clientMealOrder->payment_status = 'paid';
            $clientMealOrder->paid_amount = $clientMealOrder->payable_amount; // Mark as fully paid
            $clientMealOrder->save();

            // Check if all ClientMealOrders for this order are now paid
            $allClientPayments = ClientMealOrder::where('meal_order_id', $order_id)->get();
            $allPaid = $allClientPayments->every(function ($payment) {
                return $payment->payment_status === 'paid';
            });

            // Update main MealOrder payment status
            if ($allPaid) {
                // If all client payments are paid, update main order to paid
                $mealOrder->payment_status = 'paid';
                $mealOrder->paid_amount = $mealOrder->payable_amount;
            } else {
                // If only this client's payment is paid, update status to partially paid
                $mealOrder->payment_status = 'partially_paid';
                // Update paid amount (sum of all paid client payments)
                $totalPaid = ClientMealOrder::where('meal_order_id', $order_id)
                    ->where('payment_status', 'paid')
                    ->sum('paid_amount');
                $mealOrder->paid_amount = $totalPaid;
            }
            $mealOrder->save();

            DB::commit();

            // Prepare response message based on payment status
            $message = 'Client payment marked as paid successfully.';
            if ($allPaid) {
                $message .= ' All client payments for this order are now paid.';
            } else {
                $remainingPayments = ClientMealOrder::where('meal_order_id', $order_id)
                    ->where('payment_status', '!=', 'paid')
                    ->count();
                $message .= " {$remainingPayments} client payment(s) still pending for this order.";
            }

            return response()->json([
                'status' => 'success',
                'message' => $message,
                'data' => [
                    'client_payment' => [
                        'id' => $clientMealOrder->id,
                        'client_id' => $clientMealOrder->client_id,
                        'order_id' => $clientMealOrder->meal_order_id,
                        'payable_amount' => $clientMealOrder->payable_amount,
                        'paid_amount' => $clientMealOrder->paid_amount,
                        'payment_status' => $clientMealOrder->payment_status,
                    ],
                    'order' => [
                        'id' => $mealOrder->id,
                        'order_number' => $mealOrder->order_number,
                        'payable_amount' => $mealOrder->payable_amount,
                        'paid_amount' => $mealOrder->paid_amount,
                        'payment_status' => $mealOrder->payment_status,
                    ],
                    'summary' => [
                        'total_clients' => $allClientPayments->count(),
                        'paid_clients' => $allClientPayments->where('payment_status', 'paid')->count(),
                        'pending_clients' => $allClientPayments->where('payment_status', '!=', 'paid')->count(),
                        'is_fully_paid' => $allPaid,
                    ]
                ]
            ], 200);

        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'failed',
                'message' => 'Payment record not found.'
            ], 404);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while updating payment status.',
                'error' => env('APP_DEBUG') ? $e->getMessage() : null
            ], 500);
        }
    }
}