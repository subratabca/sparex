<?php
namespace App\Http\Controllers\Backend\Meal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\DeliveryHelper;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Config;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Exception\ApiErrorException;
use App\Models\MealDeliveryPaymentHistory;
use App\Models\DeliveryChargeLedger;
use App\Models\ClientMealOrder;
use App\Models\ClientMealPaymentHistory;
use App\Models\MealOrder;
use App\Models\MealOrderItem;
use App\Models\MealShippingAddress;
use App\Models\MealDeliveryCharge;
use App\Models\User;
use App\Models\MealType;
use Carbon\Carbon;
use Exception;


class MealPaymentController extends Controller
{
    public function clientPaymentPage()
    {
        return view('backend.pages.meal-payment-history.client.index');
    }

    public function getClientPaymentList(Request $request)
    {
        try {
            $clientOrders = ClientMealOrder::with([
                'mealOrder:id,invoice_no,created_at,customer_id',
                'mealOrder.customer:id,firstName,lastName',
                'client:id,firstName,lastName',
                'mealOrder.items:id,meal_order_id,meal_date',
            ])
            ->whereHas('mealOrder')
            ->latest()
            ->get();

            $data = $clientOrders->map(function ($item) {
                $mealOrder    = $item->mealOrder;
                $client       = $item->client;

                $customerName = '';
                if ($mealOrder && $mealOrder->customer) {
                    $customerName = trim($mealOrder->customer->firstName . ' ' . ($mealOrder->customer->lastName ?? ''));
                }

                $latestMealDate = '-';
                if ($mealOrder && $mealOrder->items && $mealOrder->items->isNotEmpty()) {
                    $maxDate = $mealOrder->items->max('meal_date');
                    if ($maxDate) {
                        try {
                            $latestMealDate = Carbon::parse($maxDate)->format('d M Y');
                        } catch (Exception $e) {
                            $latestMealDate = '-';
                        }
                    }
                }

                return [
                    'id'             => $item->id,
                    'order_id'       => $item->meal_order_id,
                    'client_id'      => $item->client_id,
                    'order_date'     => $mealOrder ? $mealOrder->created_at->format('d M Y') : '-',
                    'invoice_no'     => $mealOrder ? $mealOrder->invoice_no : '-',
                    'client_name'    => $client ? trim($client->firstName . ' ' . ($client->lastName ?? '')) : '-',
                    'customer_name'  => $customerName,
                    'meal_date'      => $latestMealDate,
                    'payable_amount' => (float) $item->payable_amount,
                    'payment_status' => $item->payment_status ?? 'unknown',
                ];
            });

            return response()->json(['status' => 'success', 'data' => $data], 200);

        } catch (Exception $e) {
            Log::error('getClientPaymentList error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'failed',
                'message' => 'An error occurred while retrieving client payments.',
            ], 500);
        }
    }

    public function clientPaymentDetailsPage()
    {
        return view('backend.pages.meal-payment-history.client.view');
    }

    public function getClientPaymentDetailsInfo(Request $request, $client_id, $order_id)
    {
        try {
            $client = User::find($client_id);
            if (!$client) {
                return response()->json(['status' => 'failed', 'message' => 'Client not found.'], 404);
            }

            $order = MealOrder::find($order_id);
            if (!$order) {
                return response()->json(['status' => 'failed', 'message' => 'Order not found.'], 404);
            }

            $clientMealOrder = ClientMealOrder::with([
                'mealOrder:id,order_number,invoice_no,status,created_at,delivery_fee,subtotal,tax,payable_amount,payment_status',
                'client:id,firstName,lastName,email,mobile,image',
            ])
            ->where('client_id',      $client_id)
            ->where('meal_order_id',  $order_id)
            ->first();

            // ← Simply return 404 — no virtual object needed
            if (!$clientMealOrder) {
                return response()->json([
                    'status'  => 'failed',
                    'message' => 'Payment record not found for this client and order.'
                ], 404);
            }

            $mealOrderItems = MealOrderItem::with([
                'mealType:id,name',
                'product:id,name,image,price',
                'client:id,firstName,lastName',
            ])
            ->where('client_id',     $client_id)
            ->where('meal_order_id', $order_id)
            ->get();

            if ($mealOrderItems->isEmpty()) {
                return response()->json([
                    'status'  => 'failed',
                    'message' => 'No items found for this client in the specified order.'
                ], 404);
            }

            $clientMealOrder->mealOrderItems = $mealOrderItems;

            // Get total paid from payment history
            $totalPaidFromHistory = ClientMealPaymentHistory::where('client_meal_order_id', $clientMealOrder->id)
                ->sum('paid_amount');

            // Group items by meal_date → meal_type
            $groupedItems = [];
            foreach ($clientMealOrder->mealOrderItems as $item) {
                $mealDate     = $item->meal_date;
                $mealTypeName = $item->mealType ? $item->mealType->name : 'Other';

                $groupedItems[$mealDate][$mealTypeName][] = [
                    'id'             => $item->id,
                    'product_name'   => $item->product ? $item->product->name : 'N/A',
                    'product_image'  => $item->product ? $item->product->image : null,
                    'unit_price'     => $item->unit_price,
                    'quantity'       => $item->quantity,
                    'total_price'    => $item->total_price,
                    'meal_type_id'   => $item->meal_type_id,
                    'meal_type_name' => $mealTypeName,
                    'client_name'    => $item->client
                        ? trim($item->client->firstName . ' ' . $item->client->lastName)
                        : 'N/A',
                ];
            }

            $mealTypeIds = $mealOrderItems->pluck('meal_type_id')->filter()->unique()->values();
            $mealTypes   = MealType::whereIn('id', $mealTypeIds)->get(['id', 'name']);

            $payableAmount = (float) ($clientMealOrder->payable_amount ?? 0);
            $balanceAmount = $payableAmount - $totalPaidFromHistory;

            $data = [
                'client_meal_order' => [
                    'id'             => $clientMealOrder->id,
                    'subtotal'       => $clientMealOrder->subtotal,
                    'tax'            => $clientMealOrder->tax,
                    'platform_fee'   => $clientMealOrder->platform_fee,
                    'delivery_fee'   => $clientMealOrder->delivery_fee,
                    'payable_amount' => $payableAmount,
                    'paid_amount'    => $totalPaidFromHistory,
                    'payment_status' => $clientMealOrder->payment_status,
                    'created_at'     => $clientMealOrder->created_at->format('d M Y H:i:s'),
                    'updated_at'     => $clientMealOrder->updated_at->format('d M Y H:i:s'),
                ],
                'order' => [
                    'order_number'   => $clientMealOrder->mealOrder->order_number,
                    'invoice_no'     => $clientMealOrder->mealOrder->invoice_no,
                    'status'         => $clientMealOrder->mealOrder->status,
                    'created_at'     => $clientMealOrder->mealOrder->created_at->format('d M Y H:i:s'),
                    'payment_status' => $clientMealOrder->mealOrder->payment_status,
                ],
                'client' => [
                    'id'     => $client->id,
                    'name'   => trim($client->firstName . ' ' . $client->lastName),
                    'email'  => $client->email,
                    'mobile' => $client->mobile,
                    'image'  => $client->image,
                ],
                'items'   => $groupedItems,
                'summary' => [
                    'total_items'      => $clientMealOrder->mealOrderItems->count(),
                    'total_dates'      => count($groupedItems),
                    'total_meal_types' => $mealTypes->count(),
                    'subtotal'         => $clientMealOrder->subtotal,
                    'tax'              => $clientMealOrder->tax,
                    'platform_fee'     => $clientMealOrder->platform_fee,
                    'delivery_fee'     => $clientMealOrder->delivery_fee,
                    'total_amount'     => $payableAmount,
                    'paid_amount'      => $totalPaidFromHistory,
                    'balance_amount'   => $balanceAmount < 0 ? 0 : $balanceAmount,
                ],
                'meal_types' => $mealTypes,
                'dates'      => array_keys($groupedItems),
            ];

            return response()->json(['status' => 'success', 'data' => $data], 200);

        } catch (Exception $e) {
            Log::error('getClientPaymentDetailsInfo: ' . $e->getMessage());
            return response()->json([
                'status'  => 'failed',
                'message' => 'An error occurred while retrieving payment details.',
            ], 500);
        }
    }

    public function storeByCashForClient(Request $request)
    {
        $request->validate([
            'client_meal_order_id' => 'required|exists:client_meal_orders,id',
            'amount'               => 'required|numeric|min:1',
        ]);

        DB::beginTransaction();
        try {
            $clientMealOrder                 = ClientMealOrder::findOrFail($request->client_meal_order_id);
            $clientMealOrder->payment_status = 'paid';
            $clientMealOrder->save();

            $transactionId = 'CASH_' . strtoupper(Str::random(8)) . date('YmdHis');

            ClientMealPaymentHistory::create([
                'client_meal_order_id' => $clientMealOrder->id,
                'type'                 => 'credit',
                'payment_method'       => 'cash',
                'paid_amount'          => $request->amount,
                'transaction_id'       => $transactionId,
                'currency'             => 'USD',
                'payment_notes'        => 'Cash payment by Admin',
            ]);

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Cash payment recorded successfully.',
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('storeByCashForClient: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to process payment: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function createPaymentIntentForClient(Request $request)
    {
        $request->validate([
            'amount'      => 'required|numeric|min:1',
            'currency'    => 'nullable|string|size:3',
            'description' => 'nullable|string',
            'metadata'    => 'nullable|array',
        ]);

        try {
            $stripeSecretKey = config('services.client_stripe.secret');
            if (empty($stripeSecretKey)) {
                throw new Exception('Stripe secret key is not configured.');
            }

            Stripe::setApiKey($stripeSecretKey);

            $paymentIntent = PaymentIntent::create([
                'amount'                    => intval($request->amount),
                'currency'                  => $request->currency ?? 'usd',
                'description'               => $request->description ?? 'Client Payment',
                'metadata'                  => $request->metadata ?? [],
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
            Log::error('createPaymentIntentForClient Stripe: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Stripe error: ' . $e->getMessage(),
            ], 500);
        } catch (Exception $e) {
            Log::error('createPaymentIntentForClient: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Could not create payment intent.',
            ], 500);
        }
    }

    public function storeByStripeForClient(Request $request)
    {
        $request->validate([
            'amount'                => 'required|numeric|min:1',
            'stripe_payment_id'     => 'required|string',
            'stripe_payment_method' => 'required|string',
            'payment_intent_id'     => 'required|string',
            'billing_name'          => 'required|string',
            'billing_email'         => 'required|email',
            'client_meal_order_id'  => 'required|exists:client_meal_orders,id',
        ]);

        DB::beginTransaction();
        try {
            // Verify Stripe payment
            $stripeSecretKey = config('services.client_stripe.secret');
            Stripe::setApiKey($stripeSecretKey);
            $paymentIntent = PaymentIntent::retrieve($request->payment_intent_id);

            if ($paymentIntent->status !== 'succeeded') {
                throw new Exception('Payment not succeeded. Status: ' . $paymentIntent->status);
            }

            // Find order first
            $clientMealOrder = ClientMealOrder::findOrFail($request->client_meal_order_id);

            // Create payment history
            $paymentHistory = ClientMealPaymentHistory::create([
                'client_meal_order_id' => $clientMealOrder->id,
                'type'                 => 'credit',
                'payment_method'       => 'stripe',
                'paid_amount'          => $request->amount,
                'transaction_id'       => $request->stripe_payment_id,
                'currency'             => 'USD',
                'payment_notes'        => 'Stripe payment. ID: ' . $request->stripe_payment_id,
            ]);

            // Update order status
            $clientMealOrder->payment_status = 'paid';
            $clientMealOrder->save();

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Stripe payment recorded successfully.',
                'data'    => ['payment_id' => $paymentHistory->id],
            ], 200);

        } catch (ApiErrorException $e) {
            DB::rollBack();
            Log::error('storeByStripeForClient Stripe: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Stripe verification failed: ' . $e->getMessage(),
            ], 500);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('storeByStripeForClient: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to record payment: ' . $e->getMessage(),
            ], 500);
        }
    }

    //Delivery payment

    public function index()
    {
        return view('backend.pages.meal-payment-history.delivery.index');
    }

    public function getMealDeliveryPayments(Request $request)
    {
        try {
            $ledgers = DeliveryChargeLedger::with([
                    'mealOrder.customer',
                    'mealType',
                    'client',
                    'deliveryPerson',
                    'statusHistories',
                ])
                ->whereNotNull('delivery_person_id')
                ->latest()
                ->get();

            $data = [];

            foreach ($ledgers as $index => $ledger) {

                // Check if this ledger has a 'delivered' status history
                $isDelivered = $ledger->statusHistories
                    ->where('delivery_charge_ledger_id', $ledger->id)
                    ->where('delivery_status', 'delivered')
                    ->isNotEmpty();

                $data[] = [
                    'sl'                        => $index + 1,
                    'delivery_charge_ledger_id' => $ledger->id,
                    'order_number'              => $ledger->mealOrder->order_number ?? 'N/A',
                    'invoice_no'                => $ledger->mealOrder->invoice_no   ?? 'N/A',
                    'meal_type_name'            => $ledger->mealType->name          ?? 'N/A',
                    'delivery_date'             => $ledger->delivery_date
                        ? Carbon::parse($ledger->delivery_date)->format('d M Y')
                        : '-',
                    'delivery_charge'           => number_format($ledger->delivery_charge    ?? 0, 2),
                    'rider_platform_fee'        => number_format($ledger->rider_platform_fee ?? 0, 2),
                    'payable_amount'            => number_format($ledger->payable_amount     ?? 0, 2),
                    'payment_status'            => $ledger->payment_status,
                    'delivery_status'           => $ledger->delivery_status,
                    'is_delivered'              => $isDelivered, // ← payment button trigger
                    'delivery_person_id'        => $ledger->delivery_person_id,
                    'delivery_person_name'      => trim(
                        ucwords(strtolower(
                            ($ledger->deliveryPerson->firstName ?? '') . ' ' .
                            ($ledger->deliveryPerson->lastName  ?? '')
                        ))
                    ) ?: 'N/A',
                    'customer_name'             => trim(
                        ($ledger->mealOrder->customer->firstName ?? '') . ' ' .
                        ($ledger->mealOrder->customer->lastName  ?? '')
                    ) ?: 'N/A',
                    'client_name'               => trim(
                        ($ledger->client->firstName ?? '') . ' ' .
                        ($ledger->client->lastName  ?? '')
                    ) ?: 'N/A',
                ];
            }

            return response()->json([
                'status' => 'success',
                'data'   => $data,
            ], 200);

        } catch (Exception $e) {
            Log::error('getMealDeliveryPayments: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function view()
    {
        return view('backend.pages.meal-payment-history.delivery.view');
    }

    public function getMealOrderPaymentDetails($delivery_charge_ledger_id)
    {
        try {
            $deliveryLedger = DeliveryChargeLedger::with([
                'mealOrder.customer',
                'client',
                'deliveryPerson',
                'mealType',
                'statusHistories' => fn($q) => $q->orderBy('created_at', 'desc'),
            ])->find($delivery_charge_ledger_id);

            if (!$deliveryLedger) {
                return response()->json([
                    'status'  => 'failed',
                    'message' => 'Delivery record not found.'
                ], 404);
            }

            $mealOrder = $deliveryLedger->mealOrder;
            if (!$mealOrder) {
                return response()->json([
                    'status'  => 'failed',
                    'message' => 'Meal order not found.'
                ], 404);
            }

            $shippingAddress = MealShippingAddress::where('meal_order_id', $deliveryLedger->meal_order_id)->first();

            $orderItems = MealOrderItem::with(['product', 'mealType'])
                ->where('meal_order_id', $deliveryLedger->meal_order_id)
                ->where('client_id',     $deliveryLedger->client_id)
                ->where('meal_type_id',  $deliveryLedger->meal_type_id)
                ->whereDate('meal_date', $deliveryLedger->delivery_date)
                ->get();

            $subtotal    = $orderItems->sum('total_price');
            $taxRate     = (float) config('services.tax_rate', 0.20);
            $tax         = round($subtotal * $taxRate, 2);
            $deliveryFee = (float) $deliveryLedger->delivery_charge;
            $total       = $subtotal + $tax + $deliveryFee;

            // Rider fee figures
            $deliveryCharge   = (float) $deliveryLedger->delivery_charge;
            $riderPlatformFee = (float) ($deliveryLedger->rider_platform_fee ?? 0);
            $payableAmount    = (float) ($deliveryLedger->payable_amount     ?? ($deliveryCharge - $riderPlatformFee));
            $riderFeeRate     = $deliveryCharge > 0
                ? round(($riderPlatformFee / $deliveryCharge) * 100, 0)
                : 0;

            $client   = $deliveryLedger->client;
            $customer = $mealOrder->customer;
            $mealType = $deliveryLedger->mealType;

            $deliveryStatuses = DeliveryChargeLedger::STATUS_LABELS;

            // Is this ledger delivered? (controls payment button)
            $isDelivered = $deliveryLedger->statusHistories
                ->where('delivery_charge_ledger_id', $deliveryLedger->id)
                ->where('delivery_status', 'delivered')
                ->isNotEmpty();

            // Get the delivered timestamp from status history
            $deliveredHistory = $deliveryLedger->statusHistories
                ->where('delivery_status', 'delivered')
                ->sortByDesc('created_at')
                ->first();

            $deliveredAt = $deliveredHistory
                ? Carbon::parse($deliveredHistory->created_at)->format('h:i A')
                : null;

            $data = [
                'delivery_info' => [
                    'id'                    => $deliveryLedger->id,
                    'tracking_number'       => $deliveryLedger->order_tracking,
                    'delivery_date'         => $deliveryLedger->delivery_date
                        ? Carbon::parse($deliveryLedger->delivery_date)->format('d M Y')
                        : '-',
                    'delivered_at'          => $deliveredAt,
                    'delivery_status'       => $deliveryLedger->delivery_status,
                    'delivery_status_label' => $deliveryStatuses[$deliveryLedger->delivery_status] ?? 'Unknown',
                    'delivery_charge'       => number_format($deliveryCharge,   2),
                    'rider_platform_fee'    => number_format($riderPlatformFee, 2),
                    'rider_platform_fee_rate' => $riderFeeRate,
                    'payable_amount'        => number_format($payableAmount,    2),
                    'payable_amount_raw'    => $payableAmount,
                    'distance_km'           => $deliveryLedger->distance_km,
                    'payment_status'        => $deliveryLedger->payment_status,
                    'is_delivered'          => $isDelivered,
                    'created_at'            => $deliveryLedger->created_at->format('d M Y H:i'),
                ],
                'restaurant_details' => $client ? [
                    'id'       => $client->id,
                    'name'     => ucwords(strtolower(trim($client->firstName . ' ' . $client->lastName))),
                    'email'    => $client->email,
                    'mobile'   => $client->mobile,
                    'address1' => $client->address1,
                    'address2' => $client->address2,
                    'zip_code' => $client->zip_code,
                    'image'    => $client->image,
                ] : null,
                'customer_details' => $customer ? [
                    'id'             => $customer->id,
                    'name'           => ucwords(strtolower(trim($customer->firstName . ' ' . $customer->lastName))),
                    'email'          => $customer->email,
                    'mobile'         => $customer->mobile,
                    'image'          => $customer->image,
                    'address1'       => $shippingAddress->address1 ?? null,
                    'address2'       => $shippingAddress->address2 ?? null,
                    'zip_code'       => $shippingAddress->zip_code ?? null,
                    'shipping_name'  => $shippingAddress->name     ?? null,
                    'shipping_phone' => $shippingAddress->phone    ?? null,
                    'shipping_email' => $shippingAddress->email    ?? null,
                ] : null,
                'delivery_person' => $deliveryLedger->deliveryPerson ? [
                    'id'       => $deliveryLedger->deliveryPerson->id,
                    'name'     => ucwords(strtolower(trim(
                        $deliveryLedger->deliveryPerson->firstName . ' ' .
                        $deliveryLedger->deliveryPerson->lastName
                    ))),
                    'email'    => $deliveryLedger->deliveryPerson->email,
                    'mobile'   => $deliveryLedger->deliveryPerson->mobile,
                    'address1' => $deliveryLedger->deliveryPerson->address1,
                    'address2' => $deliveryLedger->deliveryPerson->address2,
                    'zip_code' => $deliveryLedger->deliveryPerson->zip_code,
                    'image'    => $deliveryLedger->deliveryPerson->image,
                ] : null,
                'order_summary' => [
                    'order_number'  => $mealOrder->order_number,
                    'invoice_no'    => $mealOrder->invoice_no,
                    'subtotal'      => number_format($subtotal,    2),
                    'tax'           => number_format($tax,         2),
                    'tax_rate'      => $taxRate * 100,
                    'delivery_fee'  => number_format($deliveryFee, 2),
                    'total'         => number_format($total,       2),
                    'meal_type'     => $mealType->name ?? 'N/A',
                    'order_date'    => $mealOrder->created_at->format('d M Y H:i'),
                    'delivery_type' => $mealOrder->delivery_type,
                ],
                'order_items' => $orderItems->map(fn($item) => [
                    'id'            => $item->id,
                    'product_name'  => $item->product->name  ?? 'Unknown Product',
                    'product_image' => $item->product->image ?? null,
                    'quantity'      => $item->quantity,
                    'unit_price'    => number_format($item->unit_price,  2),
                    'total_price'   => number_format($item->total_price, 2),
                    'meal_time'     => $item->meal_time,
                ]),
                'status_history' => $deliveryLedger->statusHistories->map(fn($h) => [
                    'id'               => $h->id,
                    'delivery_status'  => $h->delivery_status,
                    'status_label'     => $deliveryStatuses[$h->delivery_status] ?? 'Unknown',
                    'notes'            => $h->notes,
                    'updated_by_label' => $h->updated_by_label,
                    'pick_up_at'       => $h->pick_up_at ? $h->pick_up_at->format('d M Y H:i') : null,
                    'created_at'       => $h->created_at->format('d M Y H:i'),
                ]),
            ];

            return response()->json(['status' => 'success', 'data' => $data], 200);

        } catch (Exception $e) {
            Log::error('getMealOrderPaymentDetails: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'An error occurred while fetching order details.'
            ], 500);
        }
    }

    public function storeByCash(Request $request)
    {
        $request->validate([
            'ledger_id' => 'required|exists:delivery_charge_ledgers,id',
            'amount'    => 'required|numeric|min:1',
        ]);

        DB::beginTransaction();
        try {
            $ledger                 = DeliveryChargeLedger::findOrFail($request->ledger_id);
            $ledger->payment_status = 'paid';
            $ledger->save();

            $transactionId = 'CASH_' . strtoupper(Str::random(8)) . date('YmdHis');

            MealDeliveryPaymentHistory::create([
                'delivery_charge_ledger_id' => $ledger->id,
                'type'                      => 'credit',
                'payment_method'            => 'cash',
                'paid_amount'               => $request->amount,
                'transaction_id'            => $transactionId,
                'currency'                  => 'USD',
                'payment_notes'             => 'Cash payment by Admin',
            ]);

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Cash payment recorded successfully.',
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('storeByCash delivery: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to process payment: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function createPaymentIntentForDelivery(Request $request)
    {
        $request->validate([
            'amount'      => 'required|numeric|min:1',
            'currency'    => 'nullable|string|size:3',
            'description' => 'nullable|string',
            'metadata'    => 'nullable|array',
        ]);

        try {
            $stripeSecretKey = config('services.delivery_stripe.secret');
            if (empty($stripeSecretKey)) {
                throw new Exception('Stripe secret key is not configured.');
            }

            Stripe::setApiKey($stripeSecretKey);

            $paymentIntent = PaymentIntent::create([
                'amount'                    => intval($request->amount),
                'currency'                  => $request->currency  ?? 'usd',
                'description'               => $request->description ?? 'Delivery Payment',
                'metadata'                  => $request->metadata  ?? [],
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
            Log::error('createPaymentIntentForDelivery Stripe: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Stripe error: ' . $e->getMessage(),
            ], 500);
        } catch (Exception $e) {
            Log::error('createPaymentIntentForDelivery: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Could not create payment intent.',
            ], 500);
        }
    }

    public function storeByStripeForDelivery(Request $request)
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

        DB::beginTransaction();
        try {
            $stripeSecretKey = config('services.delivery_stripe.secret');
            Stripe::setApiKey($stripeSecretKey);

            $paymentIntent = PaymentIntent::retrieve($request->payment_intent_id);

            if ($paymentIntent->status !== 'succeeded') {
                throw new Exception('Payment not succeeded. Status: ' . $paymentIntent->status);
            }

            $ledger = DeliveryChargeLedger::findOrFail($request->ledger_id);

            $paymentHistory = MealDeliveryPaymentHistory::create([
                'delivery_charge_ledger_id' => $ledger->id,
                'type'                      => 'credit',
                'payment_method'            => 'stripe',
                'paid_amount'               => $request->amount,
                'transaction_id'            => $request->stripe_payment_id,
                'currency'                  => 'USD',
                'payment_notes'             => 'Stripe payment. ID: ' . $request->stripe_payment_id,
            ]);

            $ledger->payment_status = 'paid';
            $ledger->save();

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Stripe payment recorded successfully.',
                'data'    => ['payment_id' => $paymentHistory->id],
            ], 200);

        } catch (ApiErrorException $e) {
            DB::rollBack();
            Log::error('storeByStripeForDelivery Stripe: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Stripe verification failed: ' . $e->getMessage(),
            ], 500);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('storeByStripeForDelivery: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to record payment: ' . $e->getMessage(),
            ], 500);
        }
    }
}