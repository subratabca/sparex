<?php
namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use App\Models\MealType;
use Exception;

class CommonController extends Controller
{
    public function getList()
    {
        try {
            $mealTypes = MealType::orderBy('id', 'desc')->get();

            return response()->json([
                'status' => 'success',
                'data' => $mealTypes
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

}
