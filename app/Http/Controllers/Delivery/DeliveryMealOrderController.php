<?php
namespace App\Http\Controllers\Delivery;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Models\Product;
use App\Models\MealType;
use App\Models\MealOrder;
use App\Models\MealOrderItem;
use App\Models\ClientMealOrder;
use App\Models\MealShippingAddress;
use App\Models\MealDeliveryCharge;
use App\Models\CreditTransaction;
use App\Models\User;
use App\Models\MealDelivery;
use App\Models\DeliveryChargeLedger;
use App\Models\MealDeliveryStatusHistory;
use Exception;

class DeliveryMealOrderController extends Controller
{
    public function updateDeliveryStatus(Request $request, $orderId)
    {
        try {
            $delivery_man_id = $request->header('id');
            
            // Validate request
            $request->validate([
                'meal_order_item_id' => 'required|string',
                'delivery_status' => 'required|string|in:picked_up,on_the_way,arrived,delivered',
                'notes' => 'nullable|string|max:500',
                'meal_date' => 'required|date',
                'meal_type_id' => 'nullable|integer|exists:meal_types,id'
            ]);

            // Delivery man can only set these statuses
            if (!in_array($request->delivery_status, ['picked_up', 'on_the_way', 'arrived', 'delivered'])) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Delivery man can only set status to: picked_up, on_the_way, arrived, or delivered.'
                ], 400);
            }

            // Parse comma-separated item IDs if multiple
            $itemIds = explode(',', $request->meal_order_item_id);
            $firstItemId = trim($itemIds[0]);
            
            // Start database transaction
            DB::beginTransaction();

            // Get the meal order and first item for validation
            $mealOrder = MealOrder::with(['customer', 'mealShippingAddress'])->find($orderId);
            
            if (!$mealOrder) {
                DB::rollBack();
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Meal order not found.'
                ], 404);
            }

            // Check first item for current status
            $firstItem = MealOrderItem::where('id', $firstItemId)
                ->where('meal_order_id', $orderId)
                ->where('meal_date', $request->meal_date)
                ->first();

            if (!$firstItem) {
                DB::rollBack();
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Meal order item not found.'
                ], 404);
            }

            // Validate status transitions for delivery man
            $currentStatus = $firstItem->delivery_status;
            $newStatus = $request->delivery_status;
            
            if (!$this->isValidDeliveryStatusTransition($currentStatus, $newStatus)) {
                DB::rollBack();
                return response()->json([
                    'status' => 'failed',
                    'message' => "Cannot change status from {$this->getDeliveryStatusLabel($currentStatus)} to {$this->getDeliveryStatusLabel($newStatus)}."
                ], 400);
            }

            // Check if delivery man is already assigned to these items
            $isAssigned = true;
            foreach ($itemIds as $itemId) {
                $itemId = trim($itemId);
                $item = MealOrderItem::find($itemId);
                
                if ($item && $item->delivery_person_id && $item->delivery_person_id != $delivery_man_id) {
                    $isAssigned = false;
                    break;
                }
            }

            // If not assigned and trying to pick up, check if anyone else is assigned
            if (!$isAssigned && $newStatus === 'picked_up') {
                DB::rollBack();
                return response()->json([
                    'status' => 'failed',
                    'message' => 'These items are already assigned to another delivery person.'
                ], 400);
            }

            // Update all items
            foreach ($itemIds as $itemId) {
                $itemId = trim($itemId);
                
                // Get the meal order item
                $mealOrderItem = MealOrderItem::where('id', $itemId)
                    ->where('meal_order_id', $orderId)
                    ->where('meal_date', $request->meal_date)
                    ->first();

                if (!$mealOrderItem) {
                    DB::rollBack();
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'Meal order item not found.'
                    ], 404);
                }

                // Update or create meal delivery record
                $mealDelivery = MealDelivery::updateOrCreate(
                    ['meal_order_item_id' => $itemId],
                    [
                        'delivery_status' => $newStatus,
                        'delivery_person_id' => $delivery_man_id,
                        'delivery_notes' => $request->notes,
                        'updated_at' => now()
                    ]
                );

                // Create status history record
                if ($mealDelivery) {
                    MealDeliveryStatusHistory::create([
                        'meal_order_item_id' => $itemId,
                        'delivery_status' => $newStatus,
                        'updated_by_id' => $delivery_man_id,
                        'updated_by_type' => 'delivery',
                        'notes' => $request->notes
                    ]);

                    // Update timestamps based on status
                    if ($newStatus === 'picked_up') {
                        $mealDelivery->update(['pickup_time' => now()]);
                    } elseif ($newStatus === 'delivered') {
                        $mealDelivery->update([
                            'actual_delivery_time' => now(),
                            'handover_time' => now()
                        ]);
                        $mealOrderItem->update(['delivered_time' => now()]);
                    } elseif ($newStatus === 'arrived') {
                        $mealDelivery->update(['handover_time' => now()]);
                    }
                    
                    // Update the meal order item
                    $mealOrderItem->update([
                        'delivery_status' => $newStatus,
                        'delivery_person_id' => $delivery_man_id
                    ]);
                }
            }

            // Also update DeliveryChargeLedger
            if (in_array($newStatus, ['delivered'])) {
                DeliveryChargeLedger::where('meal_order_id', $orderId)
                    ->where('delivery_date', $request->meal_date)
                    ->when($request->meal_type_id, function($query, $mealTypeId) {
                        return $query->where('meal_type_id', $mealTypeId);
                    })
                    ->update([
                        'delivery_status' => $newStatus,
                        'delivery_person_id' => $delivery_man_id
                    ]);
            }

            // Commit transaction
            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Delivery status updated successfully.',
                'data' => [
                    'delivery_status' => $newStatus,
                    'delivery_status_label' => $this->getDeliveryStatusLabel($newStatus),
                    'previous_status' => $currentStatus,
                    'previous_status_label' => $this->getDeliveryStatusLabel($currentStatus),
                    'assigned_delivery_person_id' => $delivery_man_id,
                    'updated_at' => now()->format('d M Y H:i:s'),
                    'updated_items_count' => count($itemIds)
                ]
            ], 200);

        } catch (Exception $e) {
            DB::rollBack();
            \Log::error('Delivery status update error: ' . $e->getMessage());
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while updating delivery status.',
                'error' => env('APP_DEBUG') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Validate status transitions for DELIVERY MAN only
     * Delivery can only change: ready_for_pickup → picked_up → on_the_way → arrived → delivered
     * Delivery man CANNOT cancel orders
     */
    private function isValidDeliveryStatusTransition($currentStatus, $newStatus)
    {
        // Define valid transitions for delivery
        $deliveryTransitions = [
            'ready_for_pickup' => ['picked_up'], // Delivery man accepts by changing to picked_up
            'picked_up' => ['on_the_way'], // Once picked up, must continue delivery
            'on_the_way' => ['arrived'],
            'arrived' => ['delivered'],
            // Delivery man CANNOT cancel
        ];
        
        // Check if transition is allowed
        if (isset($deliveryTransitions[$currentStatus])) {
            return in_array($newStatus, $deliveryTransitions[$currentStatus]);
        }
        
        return false;
    }

    private function getDeliveryStatusLabel($status)
    {
        $labels = [
            'pending' => 'Pending',
            'preparing' => 'Preparing',
            'ready_for_pickup' => 'Ready for Pickup',
            'picked_up' => 'Picked Up',
            'on_the_way' => 'On the Way',
            'arrived' => 'Arrived',
            'delivered' => 'Delivered',
            'cancelled' => 'Cancelled',
        ];

        return $labels[$status] ?? $status;
    }
}