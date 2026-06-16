<?php

namespace App\Helpers;

use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTToken
{
    // Pick the signing key for a given guard/role
    private static function keyFor(string $guard): string
    {
        return match ($guard) {
            'admin'                => config('jwt.admin_key'),
            'restaurant', 'client' => config('jwt.client_key'),
            'rider', 'delivery'    => config('jwt.delivery_key'),
            default                => config('jwt.customer_key'), // customer
        };
    }

    public static function CreateToken($userEmail, $userID, $userRole, string $guard = 'customer'): string
    {
        $payload = [
            'iss'       => 'laravel-token',
            'iat'       => time(),
            'exp'       => time() + 60 * 60 * 24 * 30,
            'userEmail' => $userEmail,
            'userID'    => $userID,
            'userRole'  => $userRole,
        ];
        return JWT::encode($payload, self::keyFor($guard), 'HS256');
    }

    public static function VerifyToken($token, string $guard = 'customer'): string|object
    {
        try {
            if ($token == null) {
                return 'unauthorized';
            }
            return JWT::decode($token, new Key(self::keyFor($guard), 'HS256'));
        } catch (Exception $e) {
            return 'unauthorized';
        }
    }
}
