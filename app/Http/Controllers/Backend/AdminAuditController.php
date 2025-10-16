<?php
namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;
use App\Models\ActivityLog;

class AdminAuditController extends Controller
{
    public function AuditLogPage()
    {
        return view('backend.pages.admin-audit.audit-list');
    }

    public function index(Request $request)
    {
        try {
            $audits = ActivityLog::with('user')->latest()->get();

            return response()->json([
                'status' => 'success',
                'data' => $audits
            ], 200); 

        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while retrieving foods',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function DetailsPage(){
        return view('backend.pages.admin-audit.details');
    }

    public function show($id)
    {
        try {
            $audit = ActivityLog::with('user')->where('id', $id)->first();
            return response()->json([
                'status' => 'success',
                'data' => $audit
            ], 200);
            
        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Failed to retrieve Terms & Conditions.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function delete(Request $request)
    {
        $id = $request->input('id');

        try {
            $deleted = ActivityLog::where('id', $id)->delete();

            if ($deleted) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Data deleted successfully.'
                ], 200);
            } 
        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Failed to delete Data.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
}