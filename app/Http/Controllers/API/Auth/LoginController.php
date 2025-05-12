<?php
namespace App\Http\Controllers\API\Auth;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function Login(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'email'    => 'required|string',
            'password' => 'required|string',
        ]);
        try {
            if (filter_var($request->email, FILTER_VALIDATE_EMAIL) !== false) {
                $user = User::withTrashed()->where('email', $request->email)->first();
                if (empty($user)) {
                    return Helper::jsonErrorResponse('User not found', 404);
                }
            }
            // Check the password
            if (! Hash::check($request->password, $user->password)) {
                return Helper::jsonErrorResponse('Invalid password', 401);
            }

            // Check if the email is verified before login is successful
            if (! $user->email_verified_at) {
                return Helper::jsonErrorResponse('Email not verified. Please verify your email before logging in.', 403);
            }

            //* Generate token if email is verified
            $token = $user->createToken('YourAppName')->plainTextToken;

            return response()->json([
                'status'     => true,
                'message'    => 'User logged in successfully.',
                'code'       => 200,
                'token_type' => 'bearer',
                'token'      => $token,
                'data'       => [
                    'id'          => $user->id,
                    'name'        => $user->name,
                    'email'       => $user->email,
                    'is_verified' => $user->is_verified,
                ],
            ], 200);
        } catch (Exception $e) {
            return Helper::jsonErrorResponse($e->getMessage(), 500);

        }
    }

    public function refreshToken(): \Illuminate\Http\JsonResponse
    {
        $refreshToken = auth('api')->refresh();

        return response()->json([
            'status'     => true,
            'message'    => 'Access token refreshed successfully.',
            'code'       => 200,
            'token_type' => 'bearer',
            'token'      => $refreshToken,
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'data'       => auth('api')->user()->load('personalizedSickle'),
        ]);
    }
}
