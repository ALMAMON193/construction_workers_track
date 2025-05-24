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
            'current_location' => 'required|string',
            'lat' => 'required|numeric',
            'long' => 'required|numeric',
        ]);

        try {
            $otp = random_int(100000, 999999);
            $otpExpiresAt = Carbon::now()->addMinutes(60);

            // Create user
            $user = User::create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'password' => Hash::make($request->input('password')),
                'employee_id' => $request->input('employee_id'),
                'otp' => $otp,
                'otp_expires_at' => $otpExpiresAt,
                'is_otp_verified' => false,
            ]);

            // Create user location
            $user->locations()->create([
                'current_location' => $request->input('current_location'),
                'name' => $request->input('current_location'),
                'lat' => $request->input('lat'),
                'long' => $request->input('long'),
                'building' => 'unknown',
                'appointment' => null,
                'floor' => null,
                'category' => 'Other',
            ]);

            // Send OTP email
            Mail::to($user->email)->send(new OtpMail($otp, $user, 'Verify Your Email Address'));

            $message = 'Register Successfully';
            return $this->sendResponse($user, $message, '', 200);
        } catch (Exception $e) {
            Log::error('Register Error', (array)$e->getMessage());
            return $this->sendError($e->getMessage());
        }
    }
    public function VerifyEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp'   => 'required|digits:6',
        ]);

        try {
            $user = User::where('email', $request->input('email'))->first();

            // Check if email has already been verified
            if (!empty($user->email_verified_at)) {
                $user->is_verified = true;
                $message = 'Email Already Verified';
                return $this->sendResponse($user, $message, '', 200); // Ensure 200 is integer
            }

            // Check if OTP code is valid
            if ((string)$user->otp !== (string)$request->input('otp')) {
                return $this->sendError('Invalid OTP code', 422); // Ensure 422 is integer
            }

            // Check if OTP has expired
            if (Carbon::parse($user->otp_expires_at)->isPast()) {
                return $this->sendError('OTP has expired. Please request a new OTP.', 422); // Ensure 422 is integer
            }

            $token = $user->createToken('YourAppName')->plainTextToken;
            // Verify the email
            $user->email_verified_at = now();
            $user->is_verified = true;
            $user->otp = null;
            $user->otp_expires_at = null;
            $user->save();
        } catch (Exception $e) {
            return $this->sendError($e->getMessage(), 500); // Ensure 500 is integer
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
                return  $this->sendError('User not found.', 404);
            }

            if ($user->email_verified_at) {
                return  $this->sendError('Email already verified.', 409);
            }

            $newOtp               = random_int(100000, 999999);
            $otpExpiresAt         = Carbon::now()->addMinutes(60);
            $user->otp            = $newOtp;
            $user->otp_expires_at = $otpExpiresAt;
            $user->save();
            Mail::to($user->email)->send(new OtpMail($newOtp, $user, 'Verify Your Email Address'));

            return  $this->sendResponse($user, 'OTP sent successfully.', '', 200);
        } catch (Exception $e) {
            return  $this->sendError($e->getMessage(), $e->getCode());
        }
    }
}
