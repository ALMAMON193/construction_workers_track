<?php

namespace App\Http\Controllers\API\Auth;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Mail\OtpMail;
use App\Helpers\Helper;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Container\Attributes\Log;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    public function register(Request $request): \Illuminate\Http\JsonResponse
    {
        dd($request->all());
        $request->validate([
            'employee_id' => 'required|string|max:10',
            'name' => 'nullable|string|max:100',
            'email' => 'required|string|email|max:150|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);
        try {
            $otp = random_int(1000, 9999);
            $otpExpiresAt = Carbon::now()->addMinutes(60);

            $user = User::create([
                'employee_id' => $request->input('employee_id'),
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'password' => Hash::make($request->input('password')),
                'otp' => $otp,
                'otp_expires_at' => $otpExpiresAt,
                'is_otp_verified' => false,
            ]);
            // Send OTP email
            Mail::to($user->email)->send(mailable: new OtpMail($otp, $user, 'Verify Your Email Address'));
            return response()->json([
                'status' => true,
                'message' => 'User successfully registered. Please verify your email to log in.',
                'code' => 201,
                'data' => $user
            ], 201);
        } catch (Exception $e) {
            return Helper::jsonErrorResponse('User registration failed', 500, [$e->getMessage()]);
        }
    }
    public function VerifyEmail(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp'   => 'required|digits:4',
        ]);

        try {
            $user = User::where('email', $request->input('email'))->first();

            if (!$user) {
                return Helper::jsonErrorResponse('User not found.', 404);
            }

            // Check if email has already been verified
            if (!empty($user->email_verified_at)) {
                return Helper::jsonResponse(true, 'Email already verified.', 200);
            }

            // Check if OTP matches
            if ((string)$user->otp !== (string)$request->input('otp')) {
                return Helper::jsonErrorResponse('Invalid OTP code', 422);
            }

            // Check if OTP has expired
            if (Carbon::parse($user->otp_expires_at)->isPast()) {
                return Helper::jsonErrorResponse('OTP has expired. Please request a new OTP.', 422);
            }

            // Verify the email
            $user->email_verified_at = now();
            $user->is_verified = true;
            $user->otp = null;
            $user->otp_expires_at = null;

            // Create token after saving user to ensure consistency
            $token = $user->createToken('YourAppName')->plainTextToken;
            $user->save();

            return response()->json([
                'status' => true,
                'message' => 'Email verification successful.',
                'code' => 200,
                'token' => $token,
                'data' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'name' => $user->name,
                    'is_verified' => $user->is_verified,
                ]
            ], 200);
        } catch (Exception $e) {
            return Helper::jsonErrorResponse('An error occurred during email verification. Please try again.', 500);
        }
    }

    public function ResendOtp(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        try {
            $user = User::where('email', $request->input('email'))->first();

            if (!$user) {
                return Helper::jsonErrorResponse('User not found.', 404);
            }

            if ($user->email_verified_at) {
                return Helper::jsonErrorResponse('Email already verified.', 409);
            }

            $newOtp = random_int(1000, 9999);
            $otpExpiresAt = Carbon::now()->addMinutes(60);

            $user->otp = $newOtp;
            $user->otp_expires_at = $otpExpiresAt;
            $user->save();

            Mail::to($user->email)->send(new OtpMail($newOtp, $user, 'Verify Your Email Address'));

            return Helper::jsonResponse(true, 'A new OTP has been sent to your email.', 200, [
                'otp_expires_at' => $otpExpiresAt->toDateTimeString(),
                'email' => $user->email,
                'otp' => $newOtp
            ]);
        } catch (Exception $e) {
            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }
}
