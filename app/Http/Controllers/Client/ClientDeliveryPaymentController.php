<?php
namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\MealDeliveryPaymentHistory;
use App\Models\DeliveryChargeLedger;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Exception\ApiErrorException;
use Exception;
use App\Models\MealShippingAddress;
use App\Models\User;
use App\Models\MealOrder;
use App\Models\MealOrderItem;
use App\Models\MealType;

class ClientDeliveryPaymentController extends Controller
{
    public function index()
    {
        return view('client.pages.meal-delivery-payment.index');
    }

    public function getMealOrderPayments(Request $request)
    {
        try {
            $client_id = $request->header('id');

            $ledgers = DeliveryChargeLedger::with([
                    'mealOrder.customer',
                    'mealType',
                    'client',
                    'deliveryPerson',
                ])
                ->where('client_id', $client_id)
                ->whereNotNull('delivery_person_id')
                ->latest()
                ->get();

            $data = [];

            foreach ($ledgers as $index => $ledger) {

                $mealTypeName = $ledger->mealType->name ?? 'N/A';

                $data[] = [
                    'sl' => $index + 1,
                    'delivery_charge_ledger_id' => $ledger->id,
                    'order_number' => $ledger->mealOrder->order_number
                        ?? 'ORD-' . str_pad($ledger->mealOrder->id, 6, '0', STR_PAD_LEFT),

                    'meal_types' => [$mealTypeName],
                    'formatted_meal_types' => $mealTypeName,

                    'meal_date' => $ledger->delivery_date,
                    'delivery_charge' => number_format($ledger->delivery_charge, 2),
                    'payment_status' => $ledger->payment_status,
                    'delivery_status' => $ledger->delivery_status,

                    'delivery_person_id' => $ledger->delivery_person_id,
                    'delivery_person_name' => trim(
                        ($ledger->deliveryPerson->firstName ?? '') . ' ' .
                        ($ledger->deliveryPerson->lastName ?? '')
                    ),

                    'customer_name' => trim(
                        ($ledger->mealOrder->customer->firstName ?? '') . ' ' .
                        ($ledger->mealOrder->customer->lastName ?? '')
                    ),

                    'client_name' => trim(
                        ($ledger->client->firstName ?? '') . ' ' .
                        ($ledger->client->lastName ?? '')
                    ),
                ];
            }

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

    public function view()
    {
        return view('client.pages.meal-delivery-payment.view');
    }

    public function getMealOrderPaymentDetails($delivery_charge_ledger_id)
    {
        try {
            $deliveryLedger = DeliveryChargeLedger::with([
                'mealOrder.customer',
                'client',
                'deliveryPerson',
                'mealType',
                'statusHistories' => function($query) {
                    $query->orderBy('created_at', 'desc');
                }
            ])->find($delivery_charge_ledger_id);

            if (!$deliveryLedger) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Delivery record not found.'
                ], 404);
            }

            $mealOrder = MealOrder::find($deliveryLedger->meal_order_id);
            
            if (!$mealOrder) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Meal order not found.'
                ], 404);
            }

            $shippingAddress = MealShippingAddress::where('meal_order_id', $deliveryLedger->meal_order_id)->first();

            $orderItems = MealOrderItem::with(['product', 'mealType'])
                ->where('meal_order_id', $deliveryLedger->meal_order_id)
                ->where('client_id', $deliveryLedger->client_id)
                ->where('meal_type_id', $deliveryLedger->meal_type_id)
                ->whereDate('meal_date', $deliveryLedger->delivery_date)
                ->get();

            $subtotal = $orderItems->sum('total_price');
            $taxRate = (float) config('services.tax_rate', 0.10);
            $tax = $subtotal * $taxRate;
            $deliveryFee = $deliveryLedger->delivery_charge;
            $total = $subtotal + $tax + $deliveryFee;

            $client = User::find($deliveryLedger->client_id);
            $customer = User::find($mealOrder->customer_id);
            $mealType = MealType::find($deliveryLedger->meal_type_id);

            $deliveryStatuses = DeliveryChargeLedger::STATUS_LABELS;

            $data = [
                'delivery_info' => [
                    'id' => $deliveryLedger->id,
                    'tracking_number' => $deliveryLedger->order_tracking,
                    'delivery_date' => $deliveryLedger->delivery_date,
                    'delivery_status' => $deliveryLedger->delivery_status,
                    'delivery_status_label' => $deliveryStatuses[$deliveryLedger->delivery_status] ?? 'Unknown',
                    'delivery_charge' => number_format($deliveryLedger->delivery_charge, 2),
                    'distance_km' => $deliveryLedger->distance_km,
                    'distance_category' => $deliveryLedger->distance_category,
                    'payment_status' => $deliveryLedger->payment_status,
                    'payment_date' => $deliveryLedger->payment_date,
                    'payment_notes' => $deliveryLedger->payment_notes,
                    'charge_key' => $deliveryLedger->charge_key,
                    'created_at' => $deliveryLedger->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $deliveryLedger->updated_at->format('Y-m-d H:i:s'),
                ],
                'restaurant_details' => $client ? [
                    'id' => $client->id,
                    'name' => $client->firstName . ' ' . $client->lastName,
                    'email' => $client->email,
                    'mobile' => $client->mobile,
                    'address1' => $client->address1,
                    'address2' => $client->address2,
                    'zip_code' => $client->zip_code,
                    'city_id' => $client->city_id,
                    'image' => $client->image,
                ] : null,
                'customer_details' => $customer ? [
                    'id' => $customer->id,
                    'name' => $customer->firstName . ' ' . $customer->lastName,
                    'email' => $customer->email,
                    'mobile' => $customer->mobile,
                    'image' => $customer->image,
                    'address1' => $shippingAddress->address1 ?? null,
                    'address2' => $shippingAddress->address2 ?? null,
                    'zip_code' => $shippingAddress->zip_code ?? null,
                    'city_id' => $shippingAddress->city_id ?? null,
                    'shipping_name' => $shippingAddress->name ?? null,
                    'shipping_phone' => $shippingAddress->phone ?? null,
                    'shipping_email' => $shippingAddress->email ?? null,
                ] : null,
                'order_summary' => [
                    'order_number' => $mealOrder->order_number,
                    'invoice_no' => $mealOrder->invoice_no,
                    'subtotal' => number_format($subtotal, 2),
                    'tax' => number_format($tax, 2),
                    'delivery_fee' => number_format($deliveryFee, 2),
                    'total' => number_format($total, 2),
                    'meal_type' => $mealType->name ?? 'N/A',
                    'order_date' => $mealOrder->created_at->format('Y-m-d H:i:s'),
                    'delivery_type' => $mealOrder->delivery_type,
                ],
                'order_items' => $orderItems->map(function($item) {
                    return [
                        'id' => $item->id,
                        'product_name' => $item->product->name ?? 'Unknown Product',
                        'product_image' => $item->product->image ?? null,
                        'quantity' => $item->quantity,
                        'unit_price' => number_format($item->unit_price, 2),
                        'total_price' => number_format($item->total_price, 2),
                        'meal_date' => $item->meal_date,
                        'meal_time' => $item->meal_time,
                        'meal_type' => $item->mealType->name ?? 'N/A',
                    ];
                }),
                'status_history' => $deliveryLedger->statusHistories->map(function($history) use ($deliveryStatuses) {
                    return [
                        'id' => $history->id,
                        'delivery_status' => $history->delivery_status,
                        'status_label' => $deliveryStatuses[$history->delivery_status] ?? 'Unknown',
                        'notes' => $history->notes,
                        'updated_by_type' => $history->updated_by_type,
                        'updated_by_label' => $history->updated_by_label,
                        'pick_up_at' => $history->pick_up_at ? $history->pick_up_at->format('Y-m-d H:i:s') : null,
                        'created_at' => $history->created_at->format('Y-m-d H:i:s'),
                    ];
                }),
                'delivery_person' => $deliveryLedger->deliveryPerson ? [
                    'id' => $deliveryLedger->deliveryPerson->id,
                    'name' => $deliveryLedger->deliveryPerson->firstName . ' ' . $deliveryLedger->deliveryPerson->lastName,
                    'email' => $deliveryLedger->deliveryPerson->email,
                    'mobile' => $deliveryLedger->deliveryPerson->mobile,
                    'address1' => $deliveryLedger->deliveryPerson->address1,
                    'address2' => $deliveryLedger->deliveryPerson->address2,
                    'zip_code' => $deliveryLedger->deliveryPerson->zip_code,
                    'city_id' => $deliveryLedger->deliveryPerson->city_id,
                    'image' => $deliveryLedger->deliveryPerson->image,
                ] : null,
            ];

            return response()->json([
                'status' => 'success',
                'data' => $data
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while fetching order details.'
            ], 500);
        }
    }

    public function storeByCash(Request $request)
    {
        $request->validate([
            'ledger_id' => 'required|exists:delivery_charge_ledgers,id',
            'amount' => 'required|numeric|min:1',
        ]);

        try {
            $ledger = DeliveryChargeLedger::findOrFail($request->ledger_id);
            $ledger->payment_status = 'paid';
            $ledger->save();

            $transactionId =  'CASH_' . strtoupper(Str::random(8)) . date('YmdHis');

            MealDeliveryPaymentHistory::create([
                'delivery_charge_ledger_id' => $ledger->id,
                'type' => 'debit', 
                'payment_method' => 'cash',
                'paid_amount' => $request->amount,
                'transaction_id' => $transactionId,
                'currency' => 'USD',
                'payment_notes' => 'Cash payment by client'
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Payment recorded successfully.'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to process payment: ' . $e->getMessage()
            ], 500);
        }
    }

    public function createPaymentIntent(Request $request)
    {
        $request->validate([
            'amount'      => 'required|numeric|min:1',    
            'currency'    => 'nullable|string|size:3',
            'description' => 'nullable|string',
            'metadata'    => 'nullable|array'
        ]);

        try {
            $stripeSecretKey = config('services.stripe.secret');
            if (empty($stripeSecretKey)) {
                throw new Exception('Stripe secret key is not configured.');
            }

            Stripe::setApiKey($stripeSecretKey);

            $paymentIntent = PaymentIntent::create([
                'amount'                    => intval($request->amount),
                'currency'                  => $request->currency ?? 'usd',
                'description'               => $request->description ?? 'Client Payment',
                'metadata'                   => $request->metadata ?? [],
                'automatic_payment_methods' => ['enabled' => true],
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Payment intent created successfully.',
                'data'    => [
                    'client_secret'     => $paymentIntent->client_secret,
                    'payment_intent_id' => $paymentIntent->id,
                    'amount'            => $paymentIntent->amount / 100,
                    'currency'          => $paymentIntent->currency,
                ]
            ], 200);

        } catch (ApiErrorException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Stripe error: ' . $e->getMessage()
            ], 500);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Could not create payment intent.'
            ], 500);
        }
    }

    public function storeByStripe(Request $request)
    {
        $request->validate([
            'amount'                => 'required|numeric|min:1',
            'stripe_payment_id'     => 'required|string',
            'stripe_payment_method' => 'required|string',
            'payment_intent_id'     => 'required|string',
            'billing_name'          => 'required|string',
            'billing_email'         => 'required|email',
            'ledger_id'             => 'required|exists:delivery_charge_ledgers,id', 
        ]);

        try {
            $stripeSecretKey = config('services.stripe.secret');
            Stripe::setApiKey($stripeSecretKey);
            $paymentIntent = PaymentIntent::retrieve($request->payment_intent_id);

            if ($paymentIntent->status !== 'succeeded') {
                throw new Exception('Payment not succeeded, status: ' . $paymentIntent->status);
            }

            $paymentHistory = MealDeliveryPaymentHistory::create([
                'delivery_charge_ledger_id' => $request->ledger_id,
                'type'                       => 'credit',
                'payment_method'             => 'stripe',
                'paid_amount'                => $request->amount,
                'transaction_id'              => $request->stripe_payment_id, 
                'currency'                    => 'USD',
                'payment_notes'                => 'Stripe payment. ID: ' . $request->stripe_payment_id,
            ]);

            $ledger = DeliveryChargeLedger::find($request->ledger_id);
            if ($ledger) {
                $ledger->payment_status = 'paid';
                $ledger->save();
            }

            return response()->json([
                'status'  => 'success',
                'message' => 'Payment recorded successfully.',
                'data'    => [
                    'payment_id' => $paymentHistory->id,
                ]
            ], 200);

        } catch (ApiErrorException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Stripe verification failed: ' . $e->getMessage()
            ], 500);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to record payment: ' . $e->getMessage()
            ], 500);
        }
    }

}