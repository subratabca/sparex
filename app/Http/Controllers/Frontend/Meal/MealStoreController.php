<?php
namespace App\Http\Controllers\Frontend\Meal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Notifications\MealOrder\NewMealOrderNotification;
use Illuminate\Support\Str;
use App\Helpers\MealOrderHelper;
use App\Models\MealOrder;
use App\Models\MealShippingAddress;
use App\Models\CreditTransaction;
use App\Models\User;
use Exception;

class MealStoreController extends Controller
{
    // ===== Shared validation rules =====
    private function baseOrderValidationRules(): array
    {
        return [
            'meal_orders'                   => 'required|array|min:1',
            'meal_orders.*.meal_date'       => 'required|date',
            'meal_orders.*.meal_time'       => 'nullable|string',
            'meal_orders.*.meal_type_id'    => 'required|integer|exists:meal_types,id',
            'meal_orders.*.product_id'      => 'required|integer|exists:products,id',
            'meal_orders.*.quantity'        => 'required|integer|min:1',
            'meal_orders.*.unit_price'      => 'required|numeric|min:0',
            'meal_orders.*.total_price'     => 'required|numeric|min:0',
            'name'                          => 'required|string|max:255',
            'email'                         => 'required|email',
            'phone'                         => 'required|string|max:20',
            'address1'                      => 'required|string|max:255',
            'address2'                      => 'nullable|string|max:255',
            'zip_code'                      => 'required|string|max:20',
            'country_id'                    => 'required|integer|exists:countries,id',
            'county_id'                     => 'required|integer|exists:counties,id',
            'city_id'                       => 'required|integer|exists:cities,id',
            'delivery_option'               => 'required|in:self_pickup,courier',
            'subtotal'                      => 'required|numeric|min:0',
            'tax'                           => 'required|numeric|min:0',
            'delivery_charge'               => 'required|numeric|min:0',
            'total_amount'                  => 'required|numeric|min:0',
        ];
    }

    // ===== Shared order preparation =====
    private function prepareOrder(Request $request): array
    {
        $customerId      = $request->header('id');
        $mealOrders      = $request->meal_orders;
        $taxRate         = (float) config('services.tax_rate',            0.20);
        $serviceFeeRate  = (float) config('services.service_fee',         0.05);
        $platformFeeRate = (float) config('services.client_platform_fee', 0.05);

        $customerShippingAddress = [
            'city_id'  => $request->city_id,
            'address1' => $request->address1,
            'zip_code' => $request->zip_code,
        ];

        $breakdown = MealOrderHelper::calculateOrderBreakdown(
            $mealOrders,
            $customerShippingAddress,
            $request->delivery_option,
            $taxRate
        );

        $overallSubtotal = array_sum($breakdown['client_subtotals']);
        $overallTax      = round($overallSubtotal * $taxRate,        2);
        $serviceFee      = round($overallSubtotal * $serviceFeeRate, 2);

        // ← Total platform fee across all clients (stored on MealOrder)
        $clientPlatformFee = MealOrderHelper::calculateClientPlatformFeeTotal(
            $breakdown['client_subtotals']
        );

        $riderPlatformFee = MealOrderHelper::calculateRiderPlatformFeeTotal(
            $breakdown['delivery_ledger_data']
        );

        $calculatedDeliveryCharge = $breakdown['total_calculated_delivery_charge'];
        $overallTotal             = $overallSubtotal + $overallTax + $serviceFee + $calculatedDeliveryCharge;

        $numbers = MealOrderHelper::generateOrderNumbers();

        return [
            'customer_id'                => $customerId,
            'meal_orders'                => $mealOrders,
            'tax_rate'                   => $taxRate,
            'service_fee_rate'           => $serviceFeeRate,
            'platform_fee_rate'          => $platformFeeRate,
            'customer_shipping_address'  => $customerShippingAddress,
            'breakdown'                  => $breakdown,
            'overall_subtotal'           => $overallSubtotal,
            'overall_tax'                => $overallTax,
            'service_fee'                => $serviceFee,
            'client_platform_fee'        => $clientPlatformFee,       
            'rider_platform_fee'         => $riderPlatformFee,  
            'calculated_delivery_charge' => $calculatedDeliveryCharge,
            'overall_total'              => $overallTotal,
            'order_number'               => $numbers['order_number'],
            'invoice_no'                 => $numbers['invoice_no'],
        ];
    }

    // ===== Shared shipping address creation =====
    private function createShippingAddress(Request $request, int $mealOrderId): void
    {
        // Geocode the shipping address so the delivery map & distance have real coordinates
        $coords = MealOrderHelper::getCoordinatesFromAddress(
            trim(implode(', ', array_filter([
                $request->address1,
                $request->zip_code,
            ])))
        );

        MealShippingAddress::create([
            'meal_order_id' => $mealOrderId,
            'name'          => $request->name,
            'email'         => $request->email,
            'phone'         => $request->phone,
            'address1'      => $request->address1,
            'address2'      => $request->address2,
            'zip_code'      => $request->zip_code,
            'country_id'    => $request->country_id,
            'county_id'     => $request->county_id,
            'city_id'       => $request->city_id,
            'latitude'      => $coords['latitude']  ?? null,
            'longitude'     => $coords['longitude'] ?? null,
        ]);
    }

    // ===== Shared post-order processing =====
    private function finalizeOrder(
        MealOrder $mealOrder,
        array     $prepared,
        Request   $request,
        string    $paymentStatus,
        float     $paidRatio
    ): array {
        $breakdown  = $prepared['breakdown'];
        $customerId = $prepared['customer_id'];

        $allOrderItems = MealOrderHelper::createOrderItems(
            $prepared['meal_orders'],
            $mealOrder->id
        );

        $clientMealOrders = MealOrderHelper::createClientMealOrders(
            $mealOrder->id,
            $breakdown['client_subtotals'],
            $breakdown['client_taxes'],
            $breakdown['client_delivery_fees'],
            $paymentStatus,
            $paidRatio
        );

        $deliveryLedgers = MealOrderHelper::createDeliveryLedgers(
            $mealOrder->id,
            $customerId,
            $breakdown['delivery_ledger_data']
        );

        return [
            'all_order_items'    => $allOrderItems,
            'client_meal_orders' => $clientMealOrders,
            'delivery_ledgers'   => $deliveryLedgers,
        ];
    }

    // ===== Shared MealOrder create data =====
    private function buildMealOrderData(array $prepared, array $overrides = []): array
    {
        return array_merge([
            'customer_id'         => $prepared['customer_id'],
            'order_number'        => $prepared['order_number'],
            'invoice_no'          => $prepared['invoice_no'],
            'status'              => 'pending',
            'subtotal'            => $prepared['overall_subtotal'],
            'tax'                 => $prepared['overall_tax'],
            'service_fee'         => $prepared['service_fee'],
            'client_platform_fee' => $prepared['client_platform_fee'], 
            'rider_platform_fee'  => $prepared['rider_platform_fee'],
            'delivery_fee'        => $prepared['calculated_delivery_charge'],
            'payable_amount'      => $prepared['overall_total'],
            'currency'            => 'USD',
        ], $overrides);
    }

    // ===== Shared success response =====
    private function buildSuccessResponse(
        MealOrder $mealOrder,
        array     $prepared,
        array     $finalized,
        string    $message,
        array     $extraData = []
    ): \Illuminate\Http\JsonResponse {
        $mealOrder->load(['items', 'clientMealOrders', 'mealShippingAddress']);
        $breakdown = $prepared['breakdown'];

        return response()->json([
            'status'  => 'success',
            'message' => $message,
            'data'    => array_merge([
                'meal_order'           => $mealOrder,
                'total_items'          => count($finalized['all_order_items']),
                'total_clients'        => count($breakdown['client_subtotals']),
                'delivery_ledgers'     => MealOrderHelper::formatLedgersForResponse($finalized['delivery_ledgers']),
                'tax_rate_used'        => $prepared['tax_rate'],
                'delivery_calculation' => [
                    'calculated_total' => $prepared['calculated_delivery_charge'],
                    'details_per_date' => $breakdown['charges_per_date'],
                ],
            ], $extraData),
            'redirect_url' => '/user/meal-order'
        ], 201);
    }

    // ===== Create Payment Intent =====
    public function createPaymentIntent(Request $request)
    {
        try {
            $request->validate([
                'amount'      => 'required|numeric|min:1',
                'currency'    => 'nullable|string|size:3',
                'description' => 'nullable|string',
                'metadata'    => 'nullable|array',
            ]);

            $stripeSecretKey = config('services.stripe.secret');

            if (empty($stripeSecretKey)) {
                throw new Exception('Stripe secret key is not configured.');
            }
            if (str_starts_with($stripeSecretKey, 'pk_')) {
                throw new Exception('Invalid Stripe key — using publishable key instead of secret key.');
            }

            \Stripe\Stripe::setApiKey($stripeSecretKey);

            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount'                    => intval($request->amount),
                'currency'                  => $request->currency  ?? 'usd',
                'description'               => $request->description ?? 'Meal Order Payment',
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

        } catch (\Stripe\Exception\AuthenticationException $e) {
            return response()->json(['status' => 'error', 'message' => 'Stripe authentication failed.'], 401);
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            return response()->json(['status' => 'error', 'message' => 'Invalid Stripe request: ' . $e->getMessage()], 400);
        } catch (Exception $e) {
            Log::error('createPaymentIntent: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ===== Store by Stripe =====
    public function storeByStripe(Request $request)
    {
        $request->validate(array_merge($this->baseOrderValidationRules(), [
            'payment_intent_id' => 'required|string',
            'stripe_payment_id' => 'required|string',
        ]));

        DB::beginTransaction();
        try {
            $prepared = $this->prepareOrder($request);

            // Verify Stripe payment
            $stripeSecretKey = config('services.stripe.secret');
            if (empty($stripeSecretKey)) throw new Exception('Stripe secret key not configured.');

            \Stripe\Stripe::setApiKey($stripeSecretKey);
            $paymentIntent = \Stripe\PaymentIntent::retrieve($request->payment_intent_id);

            if ($paymentIntent->status !== 'succeeded') {
                throw new Exception('Payment not completed. Status: ' . $paymentIntent->status);
            }

            $expectedAmount = intval($prepared['overall_total'] * 100);
            if ($paymentIntent->amount !== $expectedAmount) {
                throw new Exception("Amount mismatch. Expected: {$expectedAmount}, Got: {$paymentIntent->amount}");
            }

            $mealOrder = MealOrder::create($this->buildMealOrderData($prepared, [
                'delivery_type'  => $request->delivery_option,
                'paid_amount'    => $prepared['overall_total'],
                'payment_type'   => 'stripe',
                'payment_method' => 'stripe',
                'payment_status' => 'paid',
                'transaction_id' => $request->stripe_payment_id,
            ]));

            $this->createShippingAddress($request, $mealOrder->id);

            $finalized = $this->finalizeOrder($mealOrder, $prepared, $request, 'due', 1);

            DB::commit();

            MealOrderHelper::sendOrderNotifications(
                $mealOrder,
                $prepared['customer_id'],
                $finalized['client_meal_orders'],
                NewMealOrderNotification::class
            );

            return $this->buildSuccessResponse(
                $mealOrder,
                $prepared,
                $finalized,
                'Stripe payment completed successfully!',
                [
                    'stripe_details' => [
                        'payment_intent_id' => $paymentIntent->id,
                        'amount_paid'       => $paymentIntent->amount / 100,
                        'currency'          => $paymentIntent->currency,
                        'payment_method'    => $paymentIntent->payment_method,
                    ]
                ]
            );

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('storeByStripe: ' . $e->getMessage());
            return response()->json(['status' => 'failed', 'message' => $e->getMessage()], 500);
        }
    }

    // ===== Store by Cash =====
    public function storeByCash(Request $request)
    {
        $request->validate($this->baseOrderValidationRules());

        DB::beginTransaction();
        try {
            $prepared = $this->prepareOrder($request);

            $mealOrder = MealOrder::create($this->buildMealOrderData($prepared, [
                'delivery_type'  => $request->delivery_option,
                'paid_amount'    => 0,
                'payment_type'   => 'cash',
                'payment_method' => 'cash',
                'payment_status' => 'due',
                'transaction_id' => null,
            ]));

            $this->createShippingAddress($request, $mealOrder->id);

            $finalized = $this->finalizeOrder($mealOrder, $prepared, $request, 'due', 0);

            DB::commit();

            MealOrderHelper::sendOrderNotifications(
                $mealOrder,
                $prepared['customer_id'],
                $finalized['client_meal_orders'],
                NewMealOrderNotification::class
            );

            return $this->buildSuccessResponse(
                $mealOrder,
                $prepared,
                $finalized,
                'Cash meal order placed successfully!'
            );

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('storeByCash: ' . $e->getMessage());
            return response()->json(['status' => 'failed', 'message' => $e->getMessage()], 500);
        }
    }

    // ===== Store by Credit =====
    public function storeByCredit(Request $request)
    {
        $request->validate($this->baseOrderValidationRules());

        DB::beginTransaction();
        try {
            $prepared     = $this->prepareOrder($request);
            $customerId   = $prepared['customer_id'];
            $overallTotal = $prepared['overall_total'];

            // Check credit balance
            $currentBalance = MealOrderHelper::getCustomerCreditBalance($customerId);

            if ($currentBalance < $overallTotal) {
                return response()->json([
                    'status'  => 'failed',
                    'message' => 'Insufficient credit balance.',
                    'data'    => [
                        'current_balance' => $currentBalance,
                        'order_total'     => $overallTotal,
                        'shortfall'       => $overallTotal - $currentBalance,
                    ]
                ], 400);
            }

            $transactionId = 'CREDIT-' . Str::upper(Str::random(10));

            $mealOrder = MealOrder::create($this->buildMealOrderData($prepared, [
                'delivery_type'  => $request->delivery_option,
                'paid_amount'    => $overallTotal,
                'payment_type'   => 'credit',
                'payment_method' => 'wallet',
                'payment_status' => 'paid',
                'transaction_id' => $transactionId,
            ]));

            $this->createShippingAddress($request, $mealOrder->id);

            $finalized  = $this->finalizeOrder($mealOrder, $prepared, $request, 'due', 1);
            $newBalance = $currentBalance - $overallTotal;

            CreditTransaction::create([
                'customer_id'    => $customerId,
                'type'           => 'debit',
                'method'         => 'wallet',
                'amount'         => $overallTotal,
                'balance_after'  => $newBalance,
                'transaction_id' => $transactionId,
                'currency'       => 'USD',
                'description'    => "Payment for meal order #{$prepared['order_number']}",
            ]);

            DB::commit();

            MealOrderHelper::sendOrderNotifications(
                $mealOrder,
                $customerId,
                $finalized['client_meal_orders'],
                NewMealOrderNotification::class
            );

            return $this->buildSuccessResponse(
                $mealOrder,
                $prepared,
                $finalized,
                'Credit meal order placed successfully!',
                [
                    'credit_balance' => [
                        'previous_balance' => $currentBalance,
                        'amount_used'      => $overallTotal,
                        'new_balance'      => $newBalance,
                    ]
                ]
            );

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('storeByCredit: ' . $e->getMessage());
            return response()->json(['status' => 'failed', 'message' => $e->getMessage()], 500);
        }
    }
}