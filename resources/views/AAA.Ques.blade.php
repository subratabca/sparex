<?php
namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use App\Notifications\Customer\AccountActivationNotification;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException; 
use App\Models\User;
use App\Models\DeliveryVehicle;
use App\Models\MealDelivery;
use App\Models\CreditTransaction;
use App\Models\DeliveryChargeLedger;
use Exception;

class DeliveryActivationController extends Controller
{
    public function getDeliveryPersonDetails($delivery_person_id)
    {
        try {
            // Use correct relationships from your User model
            $delivery_person = User::with([
                'country',
                'county',
                'city',
                'deliveryVehicle',
            ])->where('role', 'delivery')->findOrFail($delivery_person_id);

            // Get delivered orders count using correct relationship
            $deliveredOrders = MealDelivery::where('delivery_person_id', $delivery_person_id)
                ->where('delivery_status', MealDelivery::STATUS_DELIVERED)
                ->count();

            $totalOrders = MealDelivery::where('delivery_person_id', $delivery_person_id)->count();

            // Get earnings from DeliveryChargeLedger (correct model)
            $totalEarnings = DeliveryChargeLedger::where('delivery_person_id', $delivery_person_id)
                ->where('payment_status', 'paid')
                ->sum('delivery_charge');

            $pendingEarnings = DeliveryChargeLedger::where('delivery_person_id', $delivery_person_id)
                ->where('payment_status', 'due')
                ->sum('delivery_charge');

            // Get recent earnings from DeliveryChargeLedger
            $recentEarnings = DeliveryChargeLedger::where('delivery_person_id', $delivery_person_id)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function($ledger) {
                    return [
                        'amount' => number_format($ledger->delivery_charge, 2),
                        'status' => $ledger->payment_status,
                        'status_badge' => $ledger->payment_status == 'paid' ? 'success' : 'warning',
                        'date' => $ledger->created_at->format('d M Y'),
                        'ledger_id' => $ledger->id,
                        'order_number' => optional($ledger->mealOrder)->order_number,
                    ];
                });

            // Format response data
            $formattedData = [
                'id' => $delivery_person->id,
                'firstName' => $delivery_person->firstName,
                'lastName' => $delivery_person->lastName,
                'email' => $delivery_person->email,
                'mobile' => $delivery_person->mobile,
                'image' => $delivery_person->image,
                // Document images from User model
                'license_image' => $delivery_person->doc_image1,
                'nid_image' => $delivery_person->doc_image2,
                'is_email_verified' => $delivery_person->is_email_verified,
                'status' => $delivery_person->status,
                'created_at' => $delivery_person->created_at->format('Y-m-d H:i:s'),
                
                // Address information
                'address1' => $delivery_person->address1,
                'address2' => $delivery_person->address2,
                'postal_code' => $delivery_person->zip_code,
                
                // Location information
                'country' => [
                    'id' => optional($delivery_person->country)->id,
                    'name' => optional($delivery_person->country)->name,
                ],
                'county' => [
                    'id' => optional($delivery_person->county)->id,
                    'name' => optional($delivery_person->county)->name,
                ],
                'city' => [
                    'id' => optional($delivery_person->city)->id,
                    'name' => optional($delivery_person->city)->name,
                ],
                
                // Vehicle information
                'has_vehicle' => !is_null($delivery_person->deliveryVehicle),
                'vehicle' => $delivery_person->deliveryVehicle ? [
                    'vehicle_type' => $delivery_person->deliveryVehicle->vehicle_type,
                    'vehicle_type_label' => $delivery_person->deliveryVehicle->vehicle_type_label ?? ucfirst(str_replace('_', ' ', $delivery_person->deliveryVehicle->vehicle_type)),
                    'registration_number' => $delivery_person->deliveryVehicle->registration_number,
                    'vehicle_brand' => $delivery_person->deliveryVehicle->vehicle_brand,
                    'vehicle_model' => $delivery_person->deliveryVehicle->vehicle_model,
                    'vehicle_color' => $delivery_person->deliveryVehicle->vehicle_color,
                    'is_active' => $delivery_person->deliveryVehicle->is_active,
                    'image' => $delivery_person->deliveryVehicle->image,
                ] : null,
                
                // Statistics
                'total_orders' => $totalOrders,
                'completed_orders' => $deliveredOrders,
                'total_earnings' => number_format($totalEarnings, 2),
                'pending_earnings' => number_format($pendingEarnings, 2),
                
                // Status texts
                'status_text' => $delivery_person->status == 1 ? 'Active' : 'Inactive',
                'status_badge' => $delivery_person->status == 1 ? 'success' : 'danger',
                'email_status_text' => $delivery_person->is_email_verified == 1 ? 'Verified' : 'Not Verified',
                
                // Recent earnings
                'recent_earnings' => $recentEarnings,
            ];

            return response()->json([
                'status' => 'success',
                'message' => 'Delivery person details fetched successfully.',
                'data' => $formattedData
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Delivery person not found.'
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch delivery person details. Error: ' . $e->getMessage()
            ], 500);
        }
    }
}