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
use Exceptio;
use App\Models\Hero;

class HeroController extends Controller
{
    public function HeroPage()
    {
        return view('backend.pages.hero.index');
    }


    public function index(Request $request)
    {
        try {
            $heroData = Hero::latest()->get();

            return response()->json([
                'status' => 'success',
                'data' => $heroData
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
        return view('backend.pages.hero.create');
    }


    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate(ValidationHelper::heroValidationRules());

            $imagePath = $request->hasFile('image')
                ? ImageHelper::processAndSaveImage($request->file('image'), 'hero')
                : null;


            $heroData = ItemHelper::prepareHeroData($request, $imagePath);
            $hero = ItemHelper::storeOrUpdateHero($heroData);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Hero information created successfully.',
                'data' => $hero,
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
                'message' => 'Hero information creation failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function show($id)
    {
        try {
            $heroData = Hero::find($id);

            if (!$heroData) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Info not found'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $heroData
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
        return view('backend.pages.hero.edit');
    }

    public function update(Request $request)
    {
        DB::beginTransaction();
        try {
            $id = $request->input('id');
            $request->validate(ValidationHelper::heroValidationRules(true));
            $hero = Hero::findOrFail($id);

            $imagePath = $request->hasFile('image')
                ? ImageHelper::processAndSaveImage($request->file('image'), 'hero', false, $hero->image)
                : $hero->image;

            $heroData = ItemHelper::prepareHeroData($request, $imagePath);
            $updatedHero = ItemHelper::storeOrUpdateHero($heroData, $hero);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Hero information updated successfully.',
                'data' => $updatedHero,
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
                'message' => 'Hero information update failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function delete(Request $request)
    {
        try {
            $hero_id = $request->input('id');
            $heroData = Hero::findOrFail($hero_id);

            if(!empty($heroData->image)){
                ImageHelper::deleteOldImages($heroData->image, 'hero');
            }

            $heroData->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Information and related images deleted successfully..'
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Hero information not found',
                'error' => $e->getMessage()
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Deletion failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}