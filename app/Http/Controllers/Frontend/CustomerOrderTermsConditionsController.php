<?php
namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TermCondition;
use App\Helpers\ActivityLogger;
use Exception;

class CustomerOrderTermsConditionsController extends Controller
{

    public function orderTermsConditionsPage()
    {
        return view('frontend.pages.terms-condition.customer-order-terms-condition-page');
    }

    public function orderTermsConditionsInfo(Request $request, $name)
    {
        try {
            $termsCondition = TermCondition::where('name', str_replace('_', ' ', $name))->first();

            if ($termsCondition) {
                ActivityLogger::beforeAuthLog(
                    'tc_access_success', 
                    'Terms & Conditions retrieved successfully.', 
                    $request, 
                    'term_conditions'
                );
                return response()->json([
                    'status' => 'success',
                    'data' => $termsCondition
                ], 200);
            } else {
                ActivityLogger::beforeAuthLog(
                    'tc_access_failed', 
                    'Failed to retrieve Terms & Conditions: T&C not found.', 
                    $request, 
                    'term_conditions'
                );
                return response()->json([
                    'status' => 'failed',
                    'message' => 'T&C not found.'
                ], 404);
            }
        } catch (Exception $e) {
            ActivityLogger::beforeAuthLog(
                'tc_access_failed', 
                'System error while retrieving Terms & Conditions: ' . $e->getMessage(), 
                $request, 
                'term_conditions'
            );
            return response()->json([
                'status' => 'failed',
                'message' => 'Failed to retrieve Terms & Conditions.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


}