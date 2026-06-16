<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\JWTToken;

class JwtAuthMiddleware
{
    /**
     * Role-aware JWT verification for all panels.
     * Usage: ->middleware('jwt.auth')            // customer (default)
     *        ->middleware('jwt.auth:admin')      // admin
     *        ->middleware('jwt.auth:restaurant') // restaurant
     *        ->middleware('jwt.auth:rider')      // rider
     */
    public function handle(Request $request, Closure $next, string $guard = 'customer'): Response
    {
        $result = JWTToken::VerifyToken($request->cookie('token'), $guard);

        if ($result === 'unauthorized') {
            $loginUrl = $this->loginUrl($guard);

            if ($request->expectsJson()) {
                return response()->json([
                    'status'   => 'unauthorized',
                    'message'  => 'Session expired. Please log in again.',
                    'redirect' => $loginUrl,
                ], 401);
            }

            Cookie::queue(Cookie::forget('token'));
            return redirect($loginUrl);
        }

        $request->headers->set('email', $result->userEmail ?? '');
        $request->headers->set('id',    $result->userID ?? '');
        $request->headers->set('role',  $result->userRole ?? '');

        return $next($request);
    }

    private function loginUrl(string $guard): string
    {
        return match ($guard) {
            'admin'                => '/admin/login',
            'restaurant', 'client' => '/restaurant/login',
            'rider', 'delivery'    => '/rider/login',
            default                => '/login',
        };
    }
}
