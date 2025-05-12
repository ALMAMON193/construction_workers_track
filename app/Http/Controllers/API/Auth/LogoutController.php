<?php

namespace App\Http\Controllers\API\Auth;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Support\Facades\Auth;


class LogoutController extends Controller
{
    public function logout(): \Illuminate\Http\JsonResponse
    {
        try {
            if (auth()->check()) {
                // For web-based Sanctum auth (cookies)
                Auth::guard('web')->logout();

                // For API token-based auth (recommended for mobile/SPAs)
                auth()->user()->currentAccessToken()->delete();

                return Helper::jsonResponse(true, 'Logged out successfully.', 200);
            }

            return Helper::jsonErrorResponse('User not authenticated', 401);
        } catch (Exception $e) {
            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }
}
