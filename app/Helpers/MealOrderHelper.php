<?php

namespace App\Helpers;

use App\Models\Product;
use App\Models\MealType;
use App\Models\MealOrder;
use App\Models\MealOrderItem;
use App\Models\ClientMealOrder;
use App\Models\MealDeliveryCharge;
use App\Models\DeliveryChargeLedger;
use App\Models\MealDeliveryStatusHistory;
use App\Models\CreditTransaction;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

class MealOrderHelper
{
    // Distance tiers — add new tiers here if needed
    private static array $distanceTiers = [
        2  => 'inside_city_2km',
        5  => 'inside_city_5km',
        10 => 'inside_city_10km',
    ];

    /**
     * Calculate distance between two locations using Google Distance Matrix API
     */
    public static function getDistanceBetweenLocations(array $clientAddress, array $shippingAddress): ?float
    {
        $apiKey = config('services.google_maps.api_key');

        if (!$apiKey) {
            Log::error('Google Maps API key not configured');
            return null;
        }

        try {
            $origin      = urlencode("{$clientAddress['address1']}, {$clientAddress['zip_code']}");
            $destination = urlencode("{$shippingAddress['address1']}, {$shippingAddress['zip_code']}");
            $url         = "https://maps.googleapis.com/maps/api/distancematrix/json?origins={$origin}&destinations={$destination}&mode=driving&units=metric&key={$apiKey}";

            $response = Http::timeout(10)->get($url);
            $data     = $response->json();

            if (isset($data['rows'][0]['elements'][0]['status']) &&
                $data['rows'][0]['elements'][0]['status'] === 'OK') {
                $distanceKm = $data['rows'][0]['elements'][0]['distance']['value'] / 1000;
                Log::info("Distance calculated: {$distanceKm} km");
                return round($distanceKm, 2);
            }

            Log::warning("Distance API status: " . ($data['rows'][0]['elements'][0]['status'] ?? 'UNKNOWN'));
            return null;

        } catch (Exception $e) {
            Log::error("Distance calculation error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Geocode an address string into coordinates via the Google Geocoding API.
     * Returns ['latitude' => float, 'longitude' => float] or null on failure.
     */
    public static function getCoordinatesFromAddress(string $address): ?array
    {
        $apiKey = config('services.google_maps.api_key');

        if (!$apiKey) {
            Log::error('Google Maps API key not configured');
            return null;
        }

        if (trim($address) === '') {
            return null;
        }

        try {
            $url = "https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($address) . "&key={$apiKey}";

            $response = Http::timeout(10)->get($url);
            $data     = $response->json();

            if (($data['status'] ?? null) === 'OK' &&
                isset($data['results'][0]['geometry']['location'])) {
                $loc = $data['results'][0]['geometry']['location'];
                Log::info("Geocoded '{$address}' -> {$loc['lat']}, {$loc['lng']}");
                return [
                    'latitude'  => round($loc['lat'], 8),
                    'longitude' => round($loc['lng'], 8),
                ];
            }

            Log::warning('Geocode API status: ' . ($data['status'] ?? 'UNKNOWN') . " for: {$address}");
            return null;

        } catch (Exception $e) {
            Log::error('Geocoding error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get delivery charge based on distance using dynamic tiers
     */
    public static function getChargeByDistance(MealDeliveryCharge $deliveryCharge, float $distance): float
    {
        $charge = $deliveryCharge->inside_city_above_10km; // default

        foreach (self::$distanceTiers as $km => $column) {
            if ($distance <= $km) {
                $charge = $deliveryCharge->$column;
                break;
            }
        }

        return $charge;
    }

    /**
     * Get distance category label based on distance
     */
    public static function getDistanceCategory(float $distance): string
    {
        foreach (self::$distanceTiers as $km => $column) {
            if ($distance <= $km) {
                return $column;
            }
        }
        return 'inside_city_above_10km';
    }

    /**
     * Calculate delivery charges, client subtotals, taxes and ledger data.
     * Returns single pass result — no double Google API calls.
     */
    public static function calculateOrderBreakdown(
        array $mealOrders,
        array $customerShippingAddress,
        string $deliveryOption,
        float $taxRate
    ): array {
        $mealOrdersByDate              = [];
        $clientSubtotals               = [];
        $clientTaxes                   = [];
        $clientDeliveryFees            = [];
        $deliveryLedgerData            = [];
        $totalCalculatedDeliveryCharge = 0;
        $chargesPerDate                = [];

        // Group by date
        foreach ($mealOrders as $item) {
            $mealOrdersByDate[$item['meal_date']][] = $item;
        }

        // Calculate per client subtotals and taxes
        foreach ($mealOrders as $item) {
            $product = Product::find($item['product_id']);
            if (!$product) continue;

            $clientId = $product->client_id;

            if (!isset($clientSubtotals[$clientId])) {
                $clientSubtotals[$clientId] = 0;
                $clientTaxes[$clientId]     = 0;
            }

            $clientSubtotals[$clientId] += $item['total_price'];
            $clientTaxes[$clientId]     += $item['total_price'] * $taxRate;
        }

        // Calculate delivery charges (only for courier)
        if ($deliveryOption === 'courier') {
            foreach ($mealOrdersByDate as $date => $itemsForDate) {
                $processedKeys = [];
                $dateCharge    = 0;

                foreach ($itemsForDate as $item) {
                    $product  = Product::find($item['product_id']);
                    if (!$product) continue;

                    $client   = User::find($product->client_id);
                    $mealType = MealType::find($item['meal_type_id']);

                    if (!$client || !$mealType) continue;

                    $key = $client->id . '_' . $mealType->id;
                    if (isset($processedKeys[$key])) continue;

                    if (!$client->city_id || !$client->address1 || !$client->zip_code) continue;

                    $clientAddress = [
                        'city_id'  => $client->city_id,
                        'address1' => $client->address1,
                        'zip_code' => $client->zip_code,
                    ];

                    $distance = self::getDistanceBetweenLocations($clientAddress, $customerShippingAddress);
                    if ($distance === null) continue;

                    $deliveryCharge = MealDeliveryCharge::where('meal_type_id', $mealType->id)
                        ->first();

                    if (!$deliveryCharge) continue;

                    $charge   = self::getChargeByDistance($deliveryCharge, $distance);
                    $category = self::getDistanceCategory($distance);

                    $processedKeys[$key]    = true;
                    $dateCharge            += $charge;

                    $clientDeliveryFees[$client->id] = ($clientDeliveryFees[$client->id] ?? 0) + $charge;

                    $deliveryLedgerData[] = [
                        'client_id'         => $client->id,
                        'meal_type_id'      => $mealType->id,
                        'delivery_date'     => $date,
                        'delivery_charge'   => $charge,
                        'distance_km'       => $distance,
                        'distance_category' => $category,
                    ];
                }

                $chargesPerDate[$date]          = $dateCharge;
                $totalCalculatedDeliveryCharge += $dateCharge;
            }
        }

        $serviceFeeRate      = (float) config('services.service_fee',          0.05);
        $platformFeeRate     = (float) config('services.client_platform_fee',  0.05);

        return [
            'meal_orders_by_date'              => $mealOrdersByDate,
            'client_subtotals'                 => $clientSubtotals,
            'client_taxes'                     => $clientTaxes,
            'client_delivery_fees'             => $clientDeliveryFees,
            'delivery_ledger_data'             => $deliveryLedgerData,
            'total_calculated_delivery_charge' => $totalCalculatedDeliveryCharge,
            'charges_per_date'                 => $chargesPerDate,
            'service_fee_rate'                 => $serviceFeeRate,
            'platform_fee_rate'                => $platformFeeRate,  // ← new
        ];
    }

    /**
     * Generate unique order number and invoice number
     */
    public static function generateOrderNumbers(): array
    {
        return [
            'order_number' => 'MO-'  . Str::upper(Str::random(8)) . '-' . time(),
            'invoice_no'   => 'INV-' . Str::upper(Str::random(6)) . '-' . time(),
        ];
    }

    /**
     * Create MealOrderItems for all cart items
     */
    public static function createOrderItems(array $mealOrders, int $mealOrderId): array
    {
        $allOrderItems = [];

        foreach ($mealOrders as $item) {
            $product = Product::find($item['product_id']);
            if (!$product) continue;

            $orderItem = MealOrderItem::create([
                'meal_order_id' => $mealOrderId,
                'client_id'     => $product->client_id,
                'meal_type_id'  => $item['meal_type_id'],
                'product_id'    => $item['product_id'],
                'meal_date'     => $item['meal_date'],
                'meal_time'     => $item['meal_time'] ?? null,
                'quantity'      => $item['quantity'],
                'unit_price'    => $item['unit_price'],
                'total_price'   => $item['total_price'],
            ]);

            $allOrderItems[] = $orderItem;
        }

        return $allOrderItems;
    }

    /**
     * Create ClientMealOrder records with platform_fee
     */
    public static function createClientMealOrders(
        int    $mealOrderId,
        array  $clientSubtotals,
        array  $clientTaxes,
        array  $clientDeliveryFees,
        string $paymentStatus = 'due',
        float  $paidRatio     = 0
    ): array {
        $clientMealOrders = [];
        $platformFeeRate  = (float) config('services.client_platform_fee', 0.05);

        foreach ($clientSubtotals as $clientId => $subtotal) {
            $deliveryFee   = $clientDeliveryFees[$clientId] ?? 0;
            $tax           = $clientTaxes[$clientId]        ?? 0;
            $platformFee   = round($subtotal * $platformFeeRate, 2);    // ← 5% of subtotal only
            $payable       = ($subtotal + $tax) - $platformFee;         // gross owed to client

            $clientMealOrder = ClientMealOrder::create([
                'meal_order_id'  => $mealOrderId,
                'client_id'      => $clientId,
                'subtotal'       => $subtotal,
                'tax'            => $tax,
                'delivery_fee'   => $deliveryFee,
                'platform_fee'   => $platformFee,    // ← new
                'payable_amount' => $payable,
                'payment_status' => $paymentStatus,
            ]);

            $clientMealOrders[] = $clientMealOrder;
        }

        return $clientMealOrders;
    }

    /**
     * Calculate total client platform fee across all clients
     * Used to store on MealOrder.client_platform_fee
     */
    public static function calculateClientPlatformFeeTotal(array $clientSubtotals): float
    {
        $platformFeeRate = (float) config('services.client_platform_fee', 0.05);
        $total           = 0;

        foreach ($clientSubtotals as $subtotal) {
            $total += round($subtotal * $platformFeeRate, 2);
        }

        return round($total, 2);
    }

    /**
     * Calculate total rider platform fee across all delivery ledgers
     * Used to store on MealOrder.rider_platform_fee
     */
    public static function calculateRiderPlatformFeeTotal(array $deliveryLedgerData): float
    {
        $riderPlatformFeeRate = (float) config('services.rider_platform_fee', 0.05);
        $total                = 0;

        foreach ($deliveryLedgerData as $ledgerItem) {
            $total += round($ledgerItem['delivery_charge'] * $riderPlatformFeeRate, 2);
        }

        return round($total, 2);
    }

    /**
     * Create DeliveryChargeLedger records and initial status history
     */
public static function createDeliveryLedgers(
    int   $mealOrderId,
    int   $customerId,
    array $deliveryLedgerData
): array {
    $deliveryLedgers      = [];
    $riderPlatformFeeRate = (float) config('services.rider_platform_fee', 0.05);

    foreach ($deliveryLedgerData as $ledgerItem) {
        $chargeKey      = DeliveryChargeLedger::generateChargeKey(
            $mealOrderId,
            $ledgerItem['client_id'],
            $ledgerItem['meal_type_id'],
            $ledgerItem['delivery_date']
        );
        $trackingNumber = DeliveryChargeLedger::generateTrackingNumber();

        $deliveryCharge    = $ledgerItem['delivery_charge'];
        $riderPlatformFee  = round($deliveryCharge * $riderPlatformFeeRate, 2);
        $payableAmount     = $deliveryCharge - $riderPlatformFee;

        $deliveryLedger = DeliveryChargeLedger::create([
            'meal_order_id'      => $mealOrderId,
            'customer_id'        => $customerId,
            'client_id'          => $ledgerItem['client_id'],
            'delivery_person_id' => null,
            'meal_type_id'       => $ledgerItem['meal_type_id'],
            'delivery_date'      => $ledgerItem['delivery_date'],
            'order_tracking'     => $trackingNumber,
            'delivery_charge'    => $deliveryCharge,
            'rider_platform_fee' => $riderPlatformFee,  // ← new
            'payable_amount'     => $payableAmount,      // ← new
            'distance_km'        => $ledgerItem['distance_km'],
            'distance_category'  => $ledgerItem['distance_category'],
            'payment_status'     => 'due',
            'is_charge_counted'  => true,
            'charge_key'         => $chargeKey,
        ]);

        MealDeliveryStatusHistory::create([
            'delivery_charge_ledger_id' => $deliveryLedger->id,
            'delivery_status'           => 'pending',
            'notes'                     => 'Order placed',
            'updated_by_id'             => $customerId,
            'updated_by_type'           => 'customer',
        ]);

        $deliveryLedgers[] = $deliveryLedger;
    }

    return $deliveryLedgers;
}

    /**
     * Send notifications to admin, customer and all clients
     */
    public static function sendOrderNotifications(
        MealOrder $mealOrder,
        int       $customerId,
        array     $clientMealOrders,
        string    $notificationClass
    ): void {
        $admin = User::where('role', 'admin')->first();
        if ($admin) {
            $admin->notify(new $notificationClass($mealOrder));
        }

        $customer = User::find($customerId);
        if ($customer) {
            $customer->notify(new $notificationClass($mealOrder));
        }

        foreach ($clientMealOrders as $clientMealOrder) {
            $client = User::find($clientMealOrder->client_id);
            if ($client) {
                $client->notify(new $notificationClass($clientMealOrder));
            }
        }
    }

    /**
     * Format delivery ledgers for API response
     */
    public static function formatLedgersForResponse(array $deliveryLedgers): array
    {
        return collect($deliveryLedgers)->map(fn($ledger) => [
            'id'              => $ledger->id,
            'tracking_number' => $ledger->order_tracking,
            'client_id'       => $ledger->client_id,
            'meal_type_id'    => $ledger->meal_type_id,
            'delivery_date'   => $ledger->delivery_date,
            'delivery_status' => $ledger->delivery_status,
            'delivery_charge' => $ledger->delivery_charge,
        ])->toArray();
    }

    /**
     * Get customer credit balance from latest transaction
     */
    public static function getCustomerCreditBalance(int $customerId): float
    {
        try {
            $latest = CreditTransaction::where('customer_id', $customerId)
                ->orderBy('created_at', 'desc')
                ->orderBy('id', 'desc')
                ->first();

            return $latest ? (float) $latest->balance_after : 0;
        } catch (Exception $e) {
            Log::error('Credit balance error: ' . $e->getMessage());
            return 0;
        }
    }
}