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
        $decoded = JWTToken::VerifyToken($token);

        // If token is missing or invalid
        if ($decoded === 'unauthorized') {
            // ✅ Delete token cookie from browser
            Cookie::queue(Cookie::forget('token'));

            // Save the intended URL if not login
            if ($request->is('user/*') && !$request->is('user/login')) {
                session(['url.intended' => url()->previous()]);
            }

            // If it’s an AJAX or API request
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'unauthorized',
                    'message' => 'Session expired. Please log in again.'
                ], 401);
            }

            // Redirect to login page
            return redirect()->route('login.page')->withErrors([
                'session_expired' => 'Your session has expired. Please log in again.'
            ]);
        }

        // ✅ Check for token expiration manually
        $currentTime = time();
        if (isset($decoded->exp) && $decoded->exp < $currentTime) {
            Cookie::queue(Cookie::forget('token'));
            return redirect()->route('login.page')->withErrors([
                'session_expired' => 'Your session has expired. Please log in again.'
            ]);
        }

        // ✅ Attach decoded data to request headers
        $request->headers->set('email', $decoded->userEmail ?? '');
        $request->headers->set('id', $decoded->userID ?? '');
        $request->headers->set('role', $decoded->userRole ?? '');

        return $next($request);
    }
}
