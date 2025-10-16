<?php
namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException; 
use Exception;
use App\Models\TermCondition;

class TermsConditionsController extends Controller
{
    public function TermsConditionsPage()
    {
        return view('backend.pages.terms-condition.terms-condition-list');
    }


    public function index(Request $request)
    {
        try {
            $termsConditions = TermCondition::latest()->get();

            return response()->json([
                'status' => 'success',
                'data' => $termsConditions
            ], 200); 

        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while retrieving foods',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function CreatePage(){
        return view('backend.pages.terms-condition.create');
    }


    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|min:3|max:30|unique:term_conditions', 
                'description' => 'required|string',
            ]);

            $termsConditions = TermCondition::create([
                'name' => $request->input('name'),
                'description' => $request->input('description'),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'T&C created successfully.',
                'data' => $termsConditions,
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Validation Failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Terms $ Conditions creation failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function DetailsPage(){
        return view('backend.pages.terms-condition.details');
    }

    public function show($id)
    {
        try {
            $termsCondition = TermCondition::where('id', $id)->first();

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


    public function EditPage(){
        return view('backend.pages.terms-condition.edit');
    }


    public function update(Request $request)
    {
        try {
            $id = $request->input('id');
            $termsCondition = TermCondition::findOrFail($id);

            $request->validate([
                'name' => 'required|string|min:3|max:20|unique:term_conditions,name,' . $id, 
                'description' => 'required|string',
            ]);

            $termsCondition->update([
                'name' => $request->input('name'),
                'description' => $request->input('description'),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Terms & Conditions updated successfully.',
                'data' => $termsCondition
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Validation Failed',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Terms & Conditions update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function delete(Request $request)
    {
        $id = $request->input('id');

        try {
            $deleted = TermCondition::where('id', $id)->delete();

            if ($deleted) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Terms & Conditions deleted successfully.'
                ], 200);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Terms & Conditions not found.'
                ], 404);
            }
        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Failed to delete Terms & Conditions.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function TermsConditionsPageByType()
    {
        return view('backend.pages.terms-condition.terms-condition-page-by-type');
    }


    public function TermsConditionsInfoByType($name)
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