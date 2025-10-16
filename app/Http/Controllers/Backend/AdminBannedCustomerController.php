<?php
namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Helpers\ActivityLogger;
use App\Models\BannedCustomer;
use Exception;

class AdminBannedCustomerController extends Controller
{
    public function bannedCustomerPage()
    {
        return view('backend.pages.banned-customer.banned-customer-list-page');
    }

    public function getBanCustomerList(Request $request)
    {
        try {
            $bannedCustomers = BannedCustomer::with('customer')->latest()->get();

            ActivityLogger::log('retrieve_banned_success', 'Successfully retrieved the banned customer list.', $request, 'banned_customers');
            return response()->json([
                'status' => 'success',
                'data' => $bannedCustomers
            ], 200); 

        } catch (Exception $e) {
            ActivityLogger::log('retrieve_banned_failed', 'An unexpected error occurred: ' . $e->getMessage(), $request, 'banned_customers');
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while retrieving banned customers',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function bannedCustomerDetailsPage()
    {
        return view('backend.pages.banned-customer.banned-customer-details-page');
    }

    public function getBannedCustomerDetails(Request $request,$banned_id)
    {
        try {
            $bannedData = BannedCustomer::with([
                'customer.country',
                'customer.county',
                'customer.city',
                'client'
            ])->where('id', $banned_id)->first();

            if (!$bannedData) {
                ActivityLogger::log('retrieve_banned_failed','Banned customer not found.',$request,'banned_customers');
                return response()->json([
                    'status' => 'failed',
                    'message' => 'No banned customer found with this ID',
                ], 404);
            }

            ActivityLogger::log('retrieve_banned_success','Banned customer details retrieved successfully.',$request,'banned_customers');
            return response()->json([
                'status' => 'success',
                'message' => 'Banned customer details retrieved successfully',
                'data' => $bannedData
            ], 200);

        } catch (Exception $e) {
            ActivityLogger::log('retrieve_banned_success','Banned customer details retrieved successfully.',$request,'banned_customers');
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while retrieving banned customer details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}