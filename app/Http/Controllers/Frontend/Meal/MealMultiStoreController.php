<?php

namespace App\Http\Controllers\Frontend\Meal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Helpers\MealOrderHelper;
use App\Notifications\MealOrder\NewMealOrderNotification;
use App\Models\MealCart;
use App\Models\MealOrder;
use App\Models\MealOrderItem;
use App\Models\MealShippingAddress;
use App\Models\DeliveryChargeLedger;
use App\Models\MealDeliveryStatusHistory;
use App\Models\MealDeliveryCharge;
use App\Models\CreditTransaction;
use App\Models\User;
use Exception;

class MealMultiStoreController extends Controller
{
    /* ===================== Public payment entry points ===================== */

    public function storeByCash(Request $request)
    {
        DB::beginTransaction();
        try {
            $customerId = $request->header('id');
            $prepared   = $this->buildFromCart($customerId);

            $result = $this->persistOrder($prepared, [
                'delivery_type'  => 'courier',
                'paid_amount'    => 0,
                'payment_type'   => 'cash',
                'payment_method' => 'cash',
                'payment_status' => 'due',
                'transaction_id' => null,
            ]);

            DB::commit();
            $this->notify($result, $customerId);

            return $this->ok($result, $prepared, 'Cash meal order placed successfully!');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('multi storeByCash: ' . $e->getMessage());
            return response()->json(['status' => 'failed', 'message' => $e->getMessage()], 500);
        }
    }

    public function storeByCredit(Request $request)
    {
        DB::beginTransaction();
        try {
            $customerId = $request->header('id');
            $prepared   = $this->buildFromCart($customerId);
            $total      = $prepared['overall_total'];

            $balance = MealOrderHelper::getCustomerCreditBalance($customerId);
            if ($balance < $total) {
                DB::rollBack();
                return response()->json([
                    'status'  => 'failed',
                    'message' => 'Insufficient credit balance.',
                    'data'    => ['current_balance' => $balance, 'order_total' => $total, 'shortfall' => round($total - $balance, 2)],
                ], 400);
            }

            $transactionId = 'CREDIT-' . Str::upper(Str::random(10));

            $result = $this->persistOrder($prepared, [
                'delivery_type'  => 'courier',
                'paid_amount'    => $total,
                'payment_type'   => 'credit',
                'payment_method' => 'wallet',
                'payment_status' => 'paid',
                'transaction_id' => $transactionId,
            ]);

            CreditTransaction::create([
                'customer_id'    => $customerId,
                'type'           => 'debit',
                'method'         => 'wallet',
                'amount'         => $total,
                'balance_after'  => round($balance - $total, 2),
                'transaction_id' => $transactionId,
                'currency'       => 'USD',
                'description'    => "Payment for meal order #{$prepared['order_number']}",
            ]);

            DB::commit();
            $this->notify($result, $customerId);

            return $this->ok($result, $prepared, 'Credit meal order placed successfully!', [
                'credit_balance' => ['previous_balance' => $balance, 'amount_used' => $total, 'new_balance' => round($balance - $total, 2)],
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('multi storeByCredit: ' . $e->getMessage());
            return response()->json(['status' => 'failed', 'message' => $e->getMessage()], 500);
        }
    }

    public function storeByStripe(Request $request)
    {
        $request->validate([
            'payment_intent_id' => 'required|string',
            'stripe_payment_id' => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            $customerId = $request->header('id');
            $prepared   = $this->buildFromCart($customerId);

            $secret = config('services.stripe.secret');
            if (empty($secret)) throw new Exception('Stripe secret key not configured.');

            \Stripe\Stripe::setApiKey($secret);
            $intent = \Stripe\PaymentIntent::retrieve($request->payment_intent_id);

            if ($intent->status !== 'succeeded') {
                throw new Exception('Payment not completed. Status: ' . $intent->status);
            }
            $expected = intval(round($prepared['overall_total'] * 100));
            if ($intent->amount !== $expected) {
                throw new Exception("Amount mismatch. Expected: {$expected}, Got: {$intent->amount}");
            }

            $result = $this->persistOrder($prepared, [
                'delivery_type'  => 'courier',
                'paid_amount'    => $prepared['overall_total'],
                'payment_type'   => 'stripe',
                'payment_method' => 'stripe',
                'payment_status' => 'paid',
                'transaction_id' => $request->stripe_payment_id,
            ]);

            DB::commit();
            $this->notify($result, $customerId);

            return $this->ok($result, $prepared, 'Stripe payment completed successfully!', [
                'stripe_details' => ['payment_intent_id' => $intent->id, 'amount_paid' => $intent->amount / 100],
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('multi storeByStripe: ' . $e->getMessage());
            return response()->json(['status' => 'failed', 'message' => $e->getMessage()], 500);
        }
    }

    /* ===================== Build order data from the cart ===================== */

    private function buildFromCart(int $customerId): array
    {
        $cart = MealCart::with(['product', 'client', 'mealType', 'location.city'])
            ->where('customer_id', $customerId)
            ->whereNotNull('location_id')
            ->orderBy('meal_date')
            ->orderBy('meal_type_id')
            ->get();

        if ($cart->isEmpty()) {
            throw new Exception('No multi-address items in your cart.');
        }

        $taxRate        = (float) config('services.tax_rate', 0.20);
        $serviceFeeRate = (float) config('services.service_fee', 0.05);

        $groups             = [];
        $subtotalAll        = 0;
        $deliveryAll        = 0;
        $clientSubtotals    = [];
        $clientTaxes        = [];
        $clientDeliveryFees = [];

        foreach ($cart->groupBy('meal_date') as $date => $dateItems) {
            foreach ($dateItems->groupBy('meal_type_id') as $mealTypeId => $typeItems) {
                $first    = $typeItems->first();
                $location = $first->location;
                if (!$location) {
                    throw new Exception('A cart item is missing its delivery location.');
                }

                // items + subtotal (per group + per client)
                $groupSubtotal = 0;
                $itemRows      = [];
                foreach ($typeItems as $it) {
                    $line                       = $it->quantity * $it->unit_price;
                    $groupSubtotal             += $line;
                    $clientSubtotals[$it->client_id] = ($clientSubtotals[$it->client_id] ?? 0) + $line;
                    $itemRows[] = [
                        'client_id'    => $it->client_id,
                        'meal_type_id' => (int) $mealTypeId,
                        'product_id'   => $it->product_id,
                        'meal_date'    => $date,
                        'meal_time'    => $it->meal_time,
                        'quantity'     => $it->quantity,
                        'unit_price'   => $it->unit_price,
                        'total_price'  => round($line, 2),
                    ];
                }

                // delivery legs: one charge per distinct restaurant → distance to THIS group's location
                $dest          = ['city_id' => $location->city_id, 'address1' => $location->address1, 'zip_code' => $location->zip_code];
                $rate          = MealDeliveryCharge::where('meal_type_id', $mealTypeId)->first();
                $legs          = [];
                $groupDelivery = 0;
                $seen          = [];
                foreach ($typeItems as $it) {
                    $client = $it->client;
                    if (!$client || in_array($client->id, $seen, true)) continue;
                    $seen[] = $client->id;
                    if (!$client->city_id || !$client->address1 || !$client->zip_code || !$rate) continue;

                    $origin   = ['city_id' => $client->city_id, 'address1' => $client->address1, 'zip_code' => $client->zip_code];
                    $distance = MealOrderHelper::getDistanceBetweenLocations($origin, $dest);
                    if ($distance === null) continue;

                    $charge                            = MealOrderHelper::getChargeByDistance($rate, $distance);
                    $groupDelivery                    += $charge;
                    $clientDeliveryFees[$client->id]   = ($clientDeliveryFees[$client->id] ?? 0) + $charge;
                    $legs[] = [
                        'client_id'         => $client->id,
                        'meal_type_id'      => (int) $mealTypeId,
                        'delivery_date'     => $date,
                        'delivery_charge'   => $charge,
                        'distance_km'       => round($distance, 2),
                        'distance_category' => MealOrderHelper::getDistanceCategory($distance),
                    ];
                }

                $subtotalAll += $groupSubtotal;
                $deliveryAll += $groupDelivery;

                $groups[] = [
                    'date'         => $date,
                    'meal_type_id' => (int) $mealTypeId,
                    'location'     => $location,
                    'items'        => $itemRows,
                    'legs'         => $legs,
                    'delivery_fee' => round($groupDelivery, 2),
                ];
            }
        }

        foreach ($clientSubtotals as $cid => $sub) {
            $clientTaxes[$cid] = round($sub * $taxRate, 2);
        }

        $allLegs = [];
        foreach ($groups as $g) foreach ($g['legs'] as $leg) $allLegs[] = $leg;

        $overallTax   = round($subtotalAll * $taxRate, 2);
        $serviceFee   = round($subtotalAll * $serviceFeeRate, 2);
        $overallTotal = round($subtotalAll + $overallTax + $serviceFee + $deliveryAll, 2);
        $numbers      = MealOrderHelper::generateOrderNumbers();

        return [
            'customer_id'          => $customerId,
            'groups'               => $groups,
            'subtotal'             => round($subtotalAll, 2),
            'tax'                  => $overallTax,
            'service_fee'          => $serviceFee,
            'delivery_total'       => round($deliveryAll, 2),
            'client_subtotals'     => $clientSubtotals,
            'client_taxes'         => $clientTaxes,
            'client_delivery_fees' => $clientDeliveryFees,
            'client_platform_fee'  => MealOrderHelper::calculateClientPlatformFeeTotal($clientSubtotals),
            'rider_platform_fee'   => MealOrderHelper::calculateRiderPlatformFeeTotal($allLegs),
            'overall_total'        => $overallTotal,
            'order_number'         => $numbers['order_number'],
            'invoice_no'           => $numbers['invoice_no'],
        ];
    }

    /* ===================== Persist everything (inside the open transaction) ===================== */

    private function persistOrder(array $p, array $overrides): array
    {
        $customer = User::find($p['customer_id']);

        $mealOrder = MealOrder::create(array_merge([
            'customer_id'         => $p['customer_id'],
            'order_number'        => $p['order_number'],
            'invoice_no'          => $p['invoice_no'],
            'status'              => 'pending',
            'subtotal'            => $p['subtotal'],
            'tax'                 => $p['tax'],
            'service_fee'         => $p['service_fee'],
            'client_platform_fee' => $p['client_platform_fee'],
            'rider_platform_fee'  => $p['rider_platform_fee'],
            'delivery_fee'        => $p['delivery_total'],
            'payable_amount'      => $p['overall_total'],
            'currency'            => 'USD',
        ], $overrides));

        $deliveryLedgerData = [];

        foreach ($p['groups'] as $g) {
            $loc    = $g['location'];
            $coords = ($loc->latitude && $loc->longitude)
                ? ['latitude' => $loc->latitude, 'longitude' => $loc->longitude]
                : (MealOrderHelper::getCoordinatesFromAddress(trim($loc->address1 . ', ' . $loc->zip_code)) ?? []);

            $address = MealShippingAddress::create([
                'meal_order_id' => $mealOrder->id,
                'meal_date'     => $g['date'],
                'meal_type_id'  => $g['meal_type_id'],
                'location_id'   => $loc->id,
                'name'          => $loc->name ?: trim(($customer->firstName ?? '') . ' ' . ($customer->lastName ?? '')),
                'email'         => $customer->email ?? null,
                'phone'         => $loc->phone ?: ($customer->mobile ?? 'N/A'),
                'address1'      => $loc->address1,
                'address2'      => $loc->address2,
                'zip_code'      => $loc->zip_code,
                'country_id'    => $loc->country_id,
                'county_id'     => $loc->county_id,
                'city_id'       => $loc->city_id,
                'latitude'      => $coords['latitude']  ?? null,
                'longitude'     => $coords['longitude'] ?? null,
                'delivery_fee'  => $g['delivery_fee'],
            ]);

            foreach ($g['items'] as $item) {
                MealOrderItem::create([
                    'meal_order_id'       => $mealOrder->id,
                    'client_id'           => $item['client_id'],
                    'meal_type_id'        => $item['meal_type_id'],
                    'delivery_address_id' => $address->id,
                    'product_id'          => $item['product_id'],
                    'meal_date'           => $item['meal_date'],
                    'meal_time'           => $item['meal_time'],
                    'quantity'            => $item['quantity'],
                    'unit_price'          => $item['unit_price'],
                    'total_price'         => $item['total_price'],
                ]);
            }

            foreach ($g['legs'] as $leg) {
                $leg['delivery_address_id'] = $address->id;
                $deliveryLedgerData[]       = $leg;
            }
        }

        $clientMealOrders = MealOrderHelper::createClientMealOrders(
            $mealOrder->id,
            $p['client_subtotals'],
            $p['client_taxes'],
            $p['client_delivery_fees'],
            'due',
            0
        );

        $this->createDeliveryLedgers($mealOrder->id, $p['customer_id'], $deliveryLedgerData);

        // clear the ordered (multi-address) cart rows
        MealCart::where('customer_id', $p['customer_id'])->whereNotNull('location_id')->delete();

        return ['order' => $mealOrder, 'client_meal_orders' => $clientMealOrders];
    }

    /* ===================== Per-leg delivery ledgers (with delivery_address_id) ===================== */

    private function createDeliveryLedgers(int $mealOrderId, int $customerId, array $data): void
    {
        $riderRate = (float) config('services.rider_platform_fee', 0.05);

        foreach ($data as $leg) {
            $chargeKey = DeliveryChargeLedger::generateChargeKey($mealOrderId, $leg['client_id'], $leg['meal_type_id'], $leg['delivery_date']);
            $tracking  = DeliveryChargeLedger::generateTrackingNumber();
            $riderFee  = round($leg['delivery_charge'] * $riderRate, 2);

            $ledger = DeliveryChargeLedger::create([
                'meal_order_id'       => $mealOrderId,
                'customer_id'         => $customerId,
                'client_id'           => $leg['client_id'],
                'delivery_person_id'  => null,
                'meal_type_id'        => $leg['meal_type_id'],
                'delivery_address_id' => $leg['delivery_address_id'],
                'delivery_date'       => $leg['delivery_date'],
                'order_tracking'      => $tracking,
                'delivery_charge'     => $leg['delivery_charge'],
                'rider_platform_fee'  => $riderFee,
                'payable_amount'      => round($leg['delivery_charge'] - $riderFee, 2),
                'distance_km'         => $leg['distance_km'],
                'distance_category'   => $leg['distance_category'],
                'payment_status'      => 'due',
                'is_charge_counted'   => true,
                'charge_key'          => $chargeKey,
            ]);

            MealDeliveryStatusHistory::create([
                'delivery_charge_ledger_id' => $ledger->id,
                'delivery_status'           => 'pending',
                'notes'                     => 'Order placed',
                'updated_by_id'             => $customerId,
                'updated_by_type'           => 'customer',
            ]);
        }
    }

    /* ===================== Helpers ===================== */

    private function notify(array $result, int $customerId): void
    {
        MealOrderHelper::sendOrderNotifications(
            $result['order'],
            $customerId,
            $result['client_meal_orders'],
            NewMealOrderNotification::class
        );
    }

    private function ok(array $result, array $prepared, string $message, array $extra = [])
    {
        return response()->json([
            'status'  => 'success',
            'message' => $message,
            'data'    => array_merge([
                'order_number' => $result['order']->order_number,
                'invoice_no'   => $result['order']->invoice_no,
                'total'        => $prepared['overall_total'],
            ], $extra),
        ], 201);
    }
}
