<?php
namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\ValidationHelper;
use App\Helpers\ImageHelper;
use App\Helpers\ItemHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException; 
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use App\Models\About;

class AboutController extends Controller
{
    public function AboutPage()
    {
        return view('backend.pages.about.index');
    }


    public function index(Request $request)
    {
        try {
            $about = About::latest()->get();

            return response()->json([
                'status' => 'success',
                'data' => $about
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
        return view('backend.pages.about.create');
    }


    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate(ValidationHelper::aboutValidationRules());

            $imagePath = $request->hasFile('image')
                ? ImageHelper::processAndSaveImage($request->file('image'), 'about')
                : null;

            $aboutData = ItemHelper::prepareAboutData($request, $imagePath);
            $about = ItemHelper::storeOrUpdateAbout($aboutData);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'About information created successfully.',
                'data' => $about,
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
                'message' => 'About information creation failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    

    public function show($id)
    {
        try {
            $about = About::find($id);

            if (!$about) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'About info not found'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $about
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
        return view('backend.pages.about.edit');
    }


    public function update(Request $request)
    {
        DB::beginTransaction();
        try {
            $id = $request->input('id');
            $request->validate(ValidationHelper::aboutValidationRules(true));

            $about = About::findOrFail($id);

            $imagePath = $request->hasFile('image')
                ? ImageHelper::processAndSaveImage($request->file('image'), 'about', false, $about->image)
                : $about->image;

            $aboutData = ItemHelper::prepareAboutData($request, $imagePath);
            $updatedAbout = ItemHelper::storeOrUpdateAbout($aboutData, $about);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'About information updated successfully.',
                'data' => $updatedAbout,
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
                'message' => 'About information update failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function delete(Request $request)
    {
        try {
            $about_id = $request->input('id');
            $about = About::findOrFail($about_id);

            if (!empty($about->image)) {
                ImageHelper::deleteOldImages($about->image, 'about');
            }

            $about->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'About information and related images deleted successfully.',
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