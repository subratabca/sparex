<?php
namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException; 
use Exception;
use App\Models\TermCondition;

class ClientProductUploadTermsConditionsController extends Controller
{

    public function productUploadTermsConditionsPage()
    {
        return view('client.pages.terms-condition.product-upload-terms-condition-page');
    }

    public function productUploadTermsConditionsInfo($name)
    {
        try {
            $termsCondition = TermCondition::where('name', str_replace('_', ' ', $name))->first();

            if ($termsCondition) {
                return response()->json([
                    'status' => 'success',
                    'data' => $termsCondition
                ], 200);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'T&C not found.'
                ], 404);
            }
        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Failed to retrieve Terms & Conditions.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}