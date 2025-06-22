<?php

namespace App\Http\Controllers\API\Auth;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Mail\OtpMail;
use Illuminate\Http\Request;
use App\Traits\ResponseTrait;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    use ResponseTrait;
    public function register(Request $request): \Illuminate\Http\JsonResponse
    {

         // Validate the request data
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:100',
            'email' => 'required|string|email|max:150|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'employee_id' => 'required|string|max:20',
            'current_location' => 'required|string',
            'lat' => 'required|numeric',
            'long' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation failed', $validator->errors()->toArray(), 422);
        }
        try {
            $otp = random_int(100000, 999999);
            $otpExpiresAt = Carbon::now()->addMinutes(10); // OTP valid for 10 minutes

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
            $success = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'employee_id' => $user->employee_id,
                'lat' => $user->locations->first()->lat,
                'long' => $user->locations->first()->long,
            ];

            return $this->sendResponse($success, 'Register Successfully Please Verify Your Email',201);
        } catch (Exception $e) {
            Log::error('Register Error', (array)$e->getMessage());
            return $this->sendError($e->getMessage());
        }
    }
    public function VerifyEmail(Request $request)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'otp' => 'required|integer|digits:6',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation failed', $validator->errors()->toArray(), 422);
        }
        try {
            // Find the user by email
            $user = User::where('email', $request->email)->first();
            if (!$user) {
                return $this->sendError('User not found', 404);
            }

            // Check if user is already verified
            if ($user->email_verified_at) {
                return $this->sendError('User already verified', 422);
            }

            // Check if OTP is valid
            if ($user->otp !== $request->otp) {
                return $this->sendError('Invalid OTP', 422);
            }
            // Update user verification status
            $user->is_otp_verified = true;
            $user->email_verified_at = Carbon::now();
            $user->otp = null;
            $user->otp_expires_at = null;
            $user->save();

            // Generate token (Ensure Laravel Sanctum is properly configured: HasApiTokens trait in User model, Sanctum middleware, and personal_access_tokens table)
            $token = $user->createToken('YourAppName')->plainTextToken;

            // Prepare success response
            $success = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_verified' => $user->is_otp_verified,
                'token' => $token
            ];

            return $this->sendResponse($success, 'Email verified successfully');
        } catch (Exception $e) {
            Log::error('Email verification error: ' . $e->getMessage());
            return $this->sendError('An error occurred during verification', 500);
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
            $user->makeHidden(['otp']);
            $message = 'OTP sent successfully.';
            return  $this->sendResponse($user, $message);
        } catch (Exception $e) {
            return  $this->sendError($e->getMessage(), $e->getCode());
        }
    }
}
