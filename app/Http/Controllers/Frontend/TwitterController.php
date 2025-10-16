<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Exception;
use App\Models\User;
use App\Helpers\JWTToken;
  
class TwitterController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function redirectToTwitter()
    {
        return Socialite::driver('twitter')->redirect();
    }
          
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function handleTwitterCallback()
    {
        try {
        
            $user = Socialite::driver('twitter')->user();
         
            $finduser = User::where('email', $user->email)->first();
         
            if($finduser){
                $token = JWTToken::CreateToken($user->email, $user->id, $user->role);
                return redirect()->to('/user/dashboard')->cookie('token', $token, 60, null, null, false, false);
         
            }else{
                $newUser = User::updateOrCreate(['email' => $user->email],[
                        'firstName' => $user->name,
                        'email'=> $user->email,
                        'role' => 'user',
                    ]);
        
                $token = JWTToken::CreateToken($user->email, $user->id, $user->role);
                return redirect()->to('/user/dashboard')->cookie('token', $token, 60, null, null, false, false);
            }
        
        } catch (Exception $e) {
            dd($e->getMessage());
        }
    }
}


