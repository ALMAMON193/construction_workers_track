<?php

namespace App\Http\Controllers\API\Auth;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Mail\OtpMail;
use Illuminate\Http\Request;
use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class RegisterController extends Controller
{
    use ResponseTrait;

    public function register(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'name' => 'nullable|string|max:100',
            'email' => 'required|string|email|max:150|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'employee_id' => 'required|string|max:20',
        ]);

        try {
            $otp = random_int(100000, 999999);
            $otpExpiresAt = Carbon::now()->addMinutes(60);

            $user = User::create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'password' => Hash::make($request->input('password')),
                'employee_id' => $request->input('employee_id'),
                'otp' => $otp,
                'otp_expires_at' => $otpExpiresAt,
                'is_otp_verified' => false,
            ]);

            // Send OTP email
            Mail::to($user->email)->send(new OtpMail($otp, $user, 'Verify Your Email Address'));

            return $this->sendResponse(
                data: $user,
                message: 'User successfully registered. Please verify your email to log in.',
                code: 201
            );
        } catch (Exception $e) {
            Log::error('Register Error', ['error' => $e->getMessage()]);
            return $this->sendError(
                error: $e->getMessage(),
                code: 500
            );
        }
    }

    public function VerifyEmail(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|digits:6',
        ]);

        try {
            $user = User::where('email', $request->input('email'))->first();

            // Check if email has already been verified
            if (!empty($user->email_verified_at)) {
                return $this->sendResponse(
                    data: $user,
                    message: 'Email already verified',
                    code: 200
                );
            }

            // Check if OTP code is valid
            if ((string)$user->otp !== (string)$request->input('otp')) {
                return $this->sendError(
                    error: 'Invalid OTP code',
                    code: 422
                );
            }

            // Check if OTP has expired
            if (Carbon::parse($user->otp_expires_at)->isPast()) {
                return $this->sendError(
                    error: 'OTP has expired. Please request a new OTP.',
                    code: 422
                );
            }

            $token = $user->createToken('YourAppName')->plainTextToken;

            // Verify the email
            $user->update([
                'email_verified_at' => now(),
                'is_verified' => true,
                'otp' => null,
                'otp_expires_at' => null,
            ]);

            return $this->sendResponse(
                data: [
                    'user' => $user,
                    'token' => $token
                ],
                message: 'Email verified successfully',
                code: 200
            );
        } catch (Exception $e) {
            return $this->sendError(
                error: $e->getMessage(),
                code: 500
            );
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
                return $this->sendError(
                    error: 'User not found',
                    code: 404
                );
            }

            if ($user->email_verified_at) {
                return $this->sendError(
                    error: 'Email already verified',
                    code: 409
                );
            }

            $newOtp = random_int(100000, 999999);
            $otpExpiresAt = Carbon::now()->addMinutes(60);

            $user->update([
                'otp' => $newOtp,
                'otp_expires_at' => $otpExpiresAt,
            ]);

            Mail::to($user->email)->send(new OtpMail($newOtp, $user, 'Verify Your Email Address'));

            return $this->sendResponse(
                data: $user,
                message: 'OTP sent successfully',
                code: 200
            );
        } catch (Exception $e) {
            return $this->sendError(
                error: $e->getMessage(),
                code: 500
            );
        }
    }
}
