<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\JWTToken;
use Illuminate\Support\Facades\Cookie;

class TokenVerificationMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->cookie('token');
        $result = JWTToken::VerifyToken($token);

        if ($result == "unauthorized") {
            Cookie::queue(Cookie::forget('token'));

            if ($request->is('user/*') && !$request->is('user/login')) {
                session(['url.intended' => url()->previous()]);
            }

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            return redirect()->route('login.page');
        } else {
            $request->headers->set('email', $result->userEmail);
            $request->headers->set('id', $result->userID);
            $request->headers->set('role', $result->userRole);
            return $next($request);
        }
    }
}