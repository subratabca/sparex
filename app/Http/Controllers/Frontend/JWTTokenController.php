<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Helpers\JWTToken;
use Illuminate\Http\Request;

class JWTTokenController extends Controller
{
    public function verifyToken(Request $request)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['status' => 'unauthorized'], 401);
        }

        $decoded = JWTToken::VerifyToken($token);

        if ($decoded === 'unauthorized') {
            return response()->json(['status' => 'unauthorized'], 401);
        }

        return response()->json(['status' => 'authorized', 'data' => $decoded]);
    }
}
