<?php

namespace App\Helpers;

use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTToken
{

    public static function CreateToken($userEmail,$userID,$userRole):string
    {
        $key = env('JWT_KEY');
        $payload = [
            'iss'=>'laravel-token',
            'iat'=>time(),
            'exp'=>time() + 60*60, //for 1 hour = 60 sec * 60 min
            'userEmail'=>$userEmail,
            'userID'=>$userID,
            'userRole'=>$userRole
        ];
        return JWT::encode($payload,$key,'HS256');
    }

    public static function VerifyToken($token):string|object
    {
        try {
            if($token == null){
                return 'unauthorized';
            }
            else{
                $key = env('JWT_KEY');
                $decode=JWT::decode($token,new Key($key,'HS256'));
                return $decode;
            }
        }
        catch (Exception $e){
            return 'unauthorized';
        }
    }


    public static function AdminCreateToken($userEmail,$userID,$userRole):string
    {
        $key =env('ADMIN_JWT_KEY');
        $payload=[
            'iss'=>'laravel-token',
            'iat'=>time(),
            'exp'=>time() + 60*60*24*30,
            'userEmail'=>$userEmail,
            'userID'=>$userID,
            'userRole'=>$userRole
        ];
        return JWT::encode($payload,$key,'HS256');
    }


    public static function AdminVerifyToken($token):string|object
    {
        try {
            if($token==null){
                return 'unauthorized';
            }
            else{
                $key =env('ADMIN_JWT_KEY');
                $decode=JWT::decode($token,new Key($key,'HS256'));
                return $decode;
            }
        }
        catch (Exception $e){
            return 'unauthorized';
        }
    }


    public static function ClientCreateToken($userEmail,$userID,$userRole):string
    {
        $key =env('CLIENT_JWT_KEY');
        $payload=[
            'iss'=>'laravel-token',
            'iat'=>time(),
            'exp'=>time() + 60*60*24*30,
            'userEmail'=>$userEmail,
            'userID'=>$userID,
            'userRole'=>$userRole
        ];
        return JWT::encode($payload,$key,'HS256');
    }


    public static function ClientVerifyToken($token):string|object
    {
        try {
            if($token==null){
                return 'unauthorized';
            }
            else{
                $key =env('CLIENT_JWT_KEY');
                $decode=JWT::decode($token,new Key($key,'HS256'));
                return $decode;
            }
        }
        catch (Exception $e){
            return 'unauthorized';
        }
    }

}
