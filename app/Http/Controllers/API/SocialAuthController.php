<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Traits\ResponseTrait;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    use ResponseTrait;
    public function SocialLogin(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'token'    => 'required',
            'provider' => 'required|in:google,facebook,apple',
        ]);

        try {
            $provider   = $request->provider;
            $socialUser = Socialite::driver($provider)->stateless()->userFromToken($request->token);
            //return response()->json($socialUser);

            if ($socialUser) {
                $user      = User::withTrashed()->where('email', $socialUser->email)->first();
                if (!empty($user->deleted_at)) {
                    return response()->json([
                        'status'  => false,
                        'message' => 'Your account has been deleted. Please contact support.',
                        'code'    => 403,
                    ], 403);
                }
                $isNewUser = false;

                if (!$user) {
                    $password = Str::random(16);
                    $user     = User::create([
                        'name'              => $socialUser->getName(),
                        'email'             => $socialUser->getEmail(),
                        'employee_id'       => sprintf('%06d', mt_rand(1, 999999)),
                        'password'          => bcrypt($password),
                        'avatar'             => $socialUser->getAvatar(),
                        'email_verified_at' => now(),
                    ]);
                    $isNewUser = true;
                }

                Auth::login($user);
                $token = $user->createToken('YourAppName')->plainTextToken;

                return response()->json([
                    'status'     => true,
                    'message'    => 'User logged in successfully.',
                    'code'       => 200,
                    'token_type' => 'bearer',
                    'token'      => $token,
                    'expires_in' => config('sanctum.expiration') * 60,
                    'data'       => $user,
                ], 200);
            } else {
                return response()->json([
                    'status'  => false,
                    'message' => 'Unable to retrieve user information from social provider.',
                    'code'    => 400,
                ], 400);
            }
        } catch (Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'An error occurred while processing your request.',
                'code'    => 500,
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
