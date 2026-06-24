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
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SocialAuthController extends Controller
{
    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

public function handleProviderCallback($provider)
{
    $provider = strtolower($provider);

    if (!in_array($provider, ['facebook', 'google', 'twitter'])) {
        return redirect()->route('login.page')->with('error', 'Unsupported provider.');
    }

    try {
        $socialUser = Socialite::driver($provider)->user();

        // Some providers (e.g. Twitter/X OAuth 2.0) don't return an email — fall back to a stable handle-based address.
        $email = $socialUser->getEmail()
            ?: (($socialUser->getNickname() ?: $provider . '_' . $socialUser->getId()) . '@' . $provider . '.local');

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'firstName'         => $socialUser->getName() ?: ($socialUser->getNickname() ?: ucfirst($provider) . ' User'),
                'role'              => 'customer',
                'status'            => 1,
                'is_email_verified' => 1,
                'password'          => Hash::make(Str::random(40)),
            ]
        );

        if ($user->wasRecentlyCreated) {
            ActivityLogger::socialAuthLog('registration_success', 'New user registered successfully via ' . ucfirst($provider), $user, 'users');
        }

        $providerData = [
            'provider'     => $provider,
            'provider_id'  => $socialUser->getId(),
            'access_token' => $socialUser->token ?? null,
        ];

        if ($provider === 'facebook') {
            FacebookUser::updateOrCreate(['user_id' => $user->id], $providerData);
        } elseif ($provider === 'google') {
            GoogleUser::updateOrCreate(['user_id' => $user->id], $providerData);
        } else { // twitter
            TwitterUser::updateOrCreate(['user_id' => $user->id], $providerData);
        }

        // Build the token from the local user's id/role (not the Socialite user's).
        $token = JWTToken::CreateToken($user->email, $user->id, $user->role);
        ActivityLogger::socialAuthLog('login_success', 'Login success via ' . ucfirst($provider), $user, 'users');

        return redirect()->to('/dashboard')->cookie('token', $token, 60, null, null, config('jwt.cookie_secure'), true, false, 'Lax');

    } catch (\Exception $e) {
        ActivityLogger::socialAuthLog('login_failed', 'Social login failed for provider ' . ucfirst($provider), null, 'users');
        return redirect()->route('login.page')->with('error', 'Something went wrong or you denied the request.');
    }
}


}


