<?php
namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Notifications\Customer\AccountActivationNotification;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\User;
use App\Models\DeliveryChargeLedger;
use Exception;

class DeliveryActivationController extends Controller
{
    public function listPage()
    {
        return view('backend.pages.delivery-person.delivery-list');
    }

    public function getList(Request $request)
    {
        try {
            $listData = User::where('role', 'delivery')
                ->with(['deliveryVehicle', 'city'])
                ->latest()
                ->get()
                ->map(function ($user) {
                    $vehicle = $user->deliveryVehicle;
                    $vehicleTypeLabel = $vehicle ? $vehicle->vehicle_type_label : 'Not specified';
                    $vehicleInfo = $vehicle ? $vehicle->vehicle_info : 'No vehicle';
                    
                    return [
                        'id' => $user->id,
                        'firstName' => $user->firstName,
                        'lastName' => $user->lastName,
                        'fullName' => trim($user->firstName . ' ' . $user->lastName),
                        'email' => $user->email,
                        'mobile' => $user->mobile,
                        'image' => $user->image,
                        'status' => $user->status,
                        'is_email_verified' => $user->is_email_verified,
                        'address1' => $user->address1,
                        'city_id' => $user->city_id,
                        'city_name' => $user->city ? $user->city->name : null,
                        
                        // Vehicle Information
                        'vehicle_type' => $vehicle ? $vehicle->vehicle_type : null,
                        'vehicle_type_label' => $vehicleTypeLabel,
                        'registration_number' => $vehicle ? $vehicle->registration_number : null,
                        'vehicle_color' => $vehicle ? $vehicle->vehicle_color : null,
                        'vehicle_brand' => $vehicle ? $vehicle->vehicle_brand : null,
                        'vehicle_model' => $vehicle ? $vehicle->vehicle_model : null,
                        'vehicle_info' => $vehicleInfo,
                        'has_vehicle' => !is_null($vehicle),
                        
                        // Dates
                        'created_at' => $user->created_at->format('Y-m-d H:i:s'),
                        'formatted_date' => $user->created_at->format('d M Y'),
                        'formatted_time' => $user->created_at->format('h:i A'),
                        
                        // Status badges
                        'status_badge' => $user->status == 1 ? 'success' : 'danger',
                        'status_text' => $user->status == 1 ? 'Active' : 'Inactive',
                        'email_status_badge' => $user->is_email_verified == 1 ? 'success' : 'warning',
                        'email_status_text' => $user->is_email_verified == 1 ? 'Verified' : 'Unverified'
                    ];
                });

            if ($listData->isEmpty()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'No delivery persons found.',
                    'data' => []
                ], 200);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Delivery list retrieved successfully',
                'data' => $listData
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while retrieving delivery persons',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function deliveryPersonDetailsPage(Request $request)
    {
        $email = $request->header('email');
        $user = User::where('email', $email)->first();

        $notification_id = $request->query('notification_id');
        if ($notification_id) {
            $notification = $user->notifications()->where('id', $notification_id)->first();

            if ($notification && is_null($notification->read_at)) {
                $notification->markAsRead();
            }
        }
        
        return view('backend.pages.delivery-person.acoount-details');
    }

    public function getDeliveryPersonDetails($delivery_person_id)
    {
        try {
            $delivery_person = User::with([
                'country',
                'county',
                'city',
                'deliveryVehicle',
            ])->where('role', 'delivery')->findOrFail($delivery_person_id);

            $deliveredOrders = DeliveryChargeLedger::where('delivery_person_id', $delivery_person_id)
                ->where('delivery_status', DeliveryChargeLedger::STATUS_DELIVERED)
                ->count();

            $totalOrders = DeliveryChargeLedger::where('delivery_person_id', $delivery_person_id)->count();

            $totalEarnings = DeliveryChargeLedger::where('delivery_person_id', $delivery_person_id)
                ->where('payment_status', 'paid')
                ->sum('delivery_charge');

            $pendingEarnings = DeliveryChargeLedger::where('delivery_person_id', $delivery_person_id)
                ->where('payment_status', 'due')
                ->sum('delivery_charge');

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

            $formattedData = [
                'id' => $delivery_person->id,
                'firstName' => $delivery_person->firstName,
                'lastName' => $delivery_person->lastName,
                'email' => $delivery_person->email,
                'mobile' => $delivery_person->mobile,
                'image' => $delivery_person->image,
                'license_image' => $delivery_person->doc_image1,
                'nid_image' => $delivery_person->doc_image2,
                'is_email_verified' => $delivery_person->is_email_verified,
                'status' => $delivery_person->status,
                'created_at' => $delivery_person->created_at->format('Y-m-d H:i:s'),
                
                'address1' => $delivery_person->address1,
                'address2' => $delivery_person->address2,
                'postal_code' => $delivery_person->zip_code,
                
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
                
                'total_orders' => $totalOrders,
                'completed_orders' => $deliveredOrders,
                'total_earnings' => number_format($totalEarnings, 2),
                'pending_earnings' => number_format($pendingEarnings, 2),
                
                // Status texts
                'status_text' => $delivery_person->status == 1 ? 'Active' : 'Inactive',
                'status_badge' => $delivery_person->status == 1 ? 'success' : 'danger',
                'email_status_text' => $delivery_person->is_email_verified == 1 ? 'Verified' : 'Not Verified',
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

    public function toggleStatus(Request $request, $id)
    {
        try {
            $request->validate([
                'status' => 'required|in:0,1'
            ]);

            $user = User::where('role', 'delivery')->find($id);
            
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Delivery person not found.'
                ], 404);
            }

            $oldStatus = $user->status;
            $newStatus = $request->status;
            
            $user->status = $newStatus;
            $user->save();

            $action = $newStatus == 1 ? 'activated' : 'deactivated';
            $statusText = $newStatus == 1 ? 'Active' : 'Inactive';
            
            return response()->json([
                'status' => 'success',
                'message' => "Delivery person account has been {$action}.",
                'data' => [
                    'id' => $user->id,
                    'status' => $user->status,
                    'status_text' => $statusText,
                    'status_badge' => $newStatus == 1 ? 'success' : 'danger'
                ]
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while updating the account status.'
            ], 500);
        }
    }

}