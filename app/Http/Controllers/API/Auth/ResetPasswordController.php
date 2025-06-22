<?php

namespace App\Http\Controllers\API\Auth;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Mail\OtpMail;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class ResetPasswordController extends Controller
{
    use ResponseTrait;

    public function forgotPassword(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        try {
            $email = $request->input('email');
            $otp = random_int(100000, 999999);
            $user = User::where('email', $email)->first();

            if (!$user) {
                return $this->sendError(
                    error: 'The provided email address is not associated with any account.',
                    code: 404
                );
            }

            Mail::to($email)->send(new OtpMail($otp, $user, 'Reset Your Password'));

            $user->update([
                'otp' => $otp,
                'otp_expires_at' => Carbon::now()->addMinutes(60),
            ]);

            return $this->sendResponse(
                data: ['email' => $user->email],
                message: 'Password reset OTP has been sent to your email address.',
                code: 200
            );

        } catch (Exception $e) {
            return $this->sendError(
                error: 'Failed to process password reset request. Please try again later.',
                code: 500,
                data: ['system_error' => $e->getMessage()]
            );
        }
    }

    public function VerifyOTP(Request $request): \Illuminate\Http\JsonResponse
    {
         $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|digits:6',
        ]);
        try {
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return $this->sendError("User Not Fount",404);
            }

            if ($user->otp !== $request->otp) {
                return $this->sendError('Invalid OTP', 401);
            }
            $token = Str::random(60);
            $user->update([
                'otp' => null,
                'otp_expires_at' => null,
                'reset_password_token' => $token,
                'reset_password_token_expire_at' => Carbon::now()->addHour(),
            ]);
            $success = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'token' => $token,
            ];

            return $this->sendResponse($success, 'OTP verified successfully. Please reset your password.', 200);
        } catch (Exception $e) {
            Log::error('Failed to verify OTP: ' . $e->getMessage());
            return $this->sendError('Failed to verify OTP', 500);
        }
    }

    public function ResetPassword(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        try {
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return $this->sendError(
                    error: 'User account not found.',
                    code: 404
                );
            }

            $tokenValid = $user->reset_password_token === $request->token &&
                $user->reset_password_token_expire_at >= Carbon::now();

            if (!$tokenValid) {
                return $this->sendError(
                    error: 'The password reset link has expired or is invalid. Please request a new one.',
                    code: 419
                );
            }

            $user->update([
                'password' => Hash::make($request->password),
                'reset_password_token' => null,
                'reset_password_token_expire_at' => null,
            ]);

            return $this->sendResponse(
                data: ['email' => $user->email],
                message: 'Your password has been reset successfully. You can now login with your new password.',
                code: 200
            );

        } catch (Exception $e) {
            return $this->sendError(
                error: 'Failed to reset password. Please try again.',
                code: 500,
                data: ['system_error' => $e->getMessage()]
            );
        }
    }
}
