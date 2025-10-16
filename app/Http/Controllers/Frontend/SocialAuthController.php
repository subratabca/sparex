<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Helpers\ActivityLogger;
use App\Models\User;
use App\Models\FacebookUser;
use App\Models\GoogleUser;
use App\Models\TwitterUser;
use App\Helpers\JWTToken;

class SocialAuthController extends Controller
{
    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

public function handleProviderCallback($provider)
{
    try {
        $socialUser = Socialite::driver($provider)->user();
        $user = User::where('email', $socialUser->getEmail())->first();

        if (!$user) {
            $user = User::create([
                'firstName' => $socialUser->getName(),
                'email' => $socialUser->getEmail(),
                'role' => 'user',
            ]);

            ActivityLogger::socialAuthLog('registration_success', 'New user registered successfully via ' . ucfirst($provider), $user, 'users');
        }

        if (strtolower($provider) === 'facebook') {
            FacebookUser::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'provider' => $provider,
                    'provider_id' => $socialUser->getId(),
                    'access_token' => $socialUser->token,
                ]
            );
        } elseif (strtolower($provider) === 'google') {
            GoogleUser::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'provider' => $provider,
                    'provider_id' => $socialUser->getId(),
                    'access_token' => $socialUser->token,
                ]
            );
        } elseif (strtolower($provider) === 'twitter') {
            TwitterUser::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'provider' => $provider,
                    'provider_id' => $socialUser->getId(),
                    'access_token' => $socialUser->token,
                ]
            );
        } else {
            return redirect()->route('login.page')->with('error', 'Unsupported provider.');
        }

        $token = JWTToken::CreateToken($user->email, $user->id, $user->role);
        ActivityLogger::socialAuthLog('login_success', 'Login success via ' . ucfirst($provider), $user, 'users');

        return redirect()->to('/user/dashboard')->cookie('token', $token, 60, null, null, false, false);

    } catch (\Exception $e) {
        ActivityLogger::socialAuthLog('login_failed', 'Social login failed for provider ' . ucfirst($provider), null, 'users');
        return redirect()->route('login.page')->with('error', 'Something went wrong or you denied the request.');
    }
}


}


