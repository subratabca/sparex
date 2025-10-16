<?php
namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\ValidationHelper;
use App\Helpers\ImageHelper;
use App\Helpers\ItemHelper;
use Illuminate\Validation\ValidationException; 
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use App\Models\SiteSetting;

class SiteSettingController extends Controller
{
    function SettingPage()
    {
        return view('backend.pages.site-setting.index');
    }


    public function index(Request $request)
    {
        try {
            $siteSetting = SiteSetting::latest()->get();

            return response()->json([
                'status' => 'success',
                'data' => $siteSetting
            ], 200); 

        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred while retrieving data',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function create()
    {
        return view('backend.pages.site-setting.create');
    }


    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate(ValidationHelper::siteSettingValidationRules());

            $logoPath = $request->hasFile('logo')
                ? ImageHelper::processAndSaveImage($request->file('logo'), 'logo')
                : null;

            $siteSettingData = ItemHelper::prepareSiteSettingData($request, $logoPath);
            $siteSetting = ItemHelper::storeOrUpdateSiteSetting($siteSettingData);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Setting created successfully.',
                'data' => $siteSetting,
            ], 201);

        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'failed',
                'message' => 'Validation Failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'failed',
                'message' => 'Setting creation failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function show($id)
    {
        try {
            $setting = SiteSetting::find($id);

            if (!$setting) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Setting info not found'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $setting
            ], 200);
            
        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function EditPage()
    {
        return view('backend.pages.site-setting.edit');
    }


    public function update(Request $request)
    {
        DB::beginTransaction();
        try {
            $id = $request->input('id');
            $request->validate(ValidationHelper::siteSettingValidationRules(true, $id));

            $siteSetting = ItemHelper::findSiteSettingById($id);

            $logoPath = $request->hasFile('logo')
                ? ImageHelper::processAndSaveImage($request->file('logo'), 'logo', false, $siteSetting->logo)
                : $siteSetting->logo;

            $siteSettingData = ItemHelper::prepareSiteSettingData($request, $logoPath);
            $updatedSiteSetting = ItemHelper::storeOrUpdateSiteSetting($siteSettingData, $siteSetting);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Setting updated successfully.',
                'data' => $updatedSiteSetting,
            ], 200);

        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'failed',
                'message' => 'Validation Failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'failed',
                'message' => 'Setting update failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }



    public function delete(Request $request)
    {
        try {
            $setting_id = $request->input('id');
            $siteSetting = SiteSetting::findOrFail($setting_id);

            if(!empty($siteSetting->logo)){
                ImageHelper::deleteOldImages($siteSetting->logo, 'logo');
            }

            $siteSetting->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Site setting and related images deleted successfully.',
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'About information not found.',
                'error' => $e->getMessage(),
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Deletion failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    
}