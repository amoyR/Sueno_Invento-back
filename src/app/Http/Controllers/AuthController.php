<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\SocialAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function redirectToGoogleSignUp()
    {
        return Socialite::driver('google')
            ->stateless()
            ->with(['prompt' => 'select_account'])
            ->redirect();
    }

    public function redirectToGoogleSignIn()
    {
        return Socialite::driver('google')
            ->stateless()
            ->redirect();
    }

    public function handleGoogleCallback(Request $request)
    {
        try {
            $socialUser = Socialite::driver('google')->stateless()->user();

            $socialAccount = SocialAccount::where('provider_name', 'google')
                ->where('provider_id', $socialUser->getId())
                ->first();

            if ($socialAccount) {
                // 既存のアカウントが見つかった場合（Sign In）
                $user = $socialAccount->user;
                $isNewUser = false;
            } else {
                // 新規アカウント作成（Sign Up）
                $user = User::where('email', $socialUser->getEmail())->first();

                if (!$user) {
                    $user = User::create([
                        'name' => $socialUser->getName(),
                        'email' => $socialUser->getEmail(),
                    ]);
                }

                $user->socialAccounts()->create([
                    'provider_name' => 'google',
                    'provider_id' => $socialUser->getId(),
                    'token' => $socialUser->token,
                    'refresh_token' => $socialUser->refreshToken,
                    'token_expires_at' => $socialUser->expiresIn ? now()->addSeconds($socialUser->expiresIn) : null,
                ]);

                $isNewUser = true;
            }

            Auth::login($user);
            $token = $user->createToken('auth-token')->plainTextToken;

            return response()->json([
                'user' => $user,
                'token' => $token,
                'isNewUser' => $isNewUser,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Google authentication failed'], 401);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    }

    public function user(Request $request)
    {
        return response()->json($request->user());
    }
}
