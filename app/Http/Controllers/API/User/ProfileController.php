<?php

namespace App\Http\Controllers\API\User;

use Exception;
use App\Models\User;
use App\Helpers\Helper;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Traits\ResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;


class ProfileController extends Controller
{
    use ResponseTrait;

    public function viewProfile()
    {
        try {
            $user = User::where('id', auth()->id())
                ->firstOrFail()
                ->only([
                    'id',
                    'employee_id',
                    'name',
                    'email',
                    'phone',
                    'country_code',
                    'address',
                    'avatar',
                    'dob',
                    'gender'
                ]);

            return $this->sendResponse(
                data: $user,
                message: 'Profile fetched successfully',
                code: 200
            );
        } catch (Exception $e) {
            Log::error('Profile fetch failed: ' . $e->getMessage());
            return $this->sendError(
                error: 'Failed to fetch profile',
                code: 500
            );
        }
    }

    /**
     * Update user profile
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        // Validate incoming data
        $validatedData = $request->validate([
            'name' => 'required',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:500',
            'country_code' => 'required|string|max:5',
            'employee_id' => 'required|string|max:50',
            'gender' => 'nullable|in:male,female',
            'dob' => 'nullable|date',
        ]);
        if (!$user) {
            return $this->sendError(
                error: 'User not found',
                code: 404
            );
        }
        try {

            // Update user data
            $updatedData = $request->only([
                'name',
                'dob',
                'gender',
                'phone',
                'country_code',
                'address',
                'employee_id'
            ]);
            $user->update($updatedData);

            return $this->sendResponse(
                data: $updatedData,
                message: 'Profile updated successfully.',
                code: 200
            );
        } catch (Exception $e) {
            Log::error('Profile update failed for user ' . $user->id . ': ' . $e->getMessage());
            return $this->sendError(
                error: 'Failed to update profile: ' . $e->getMessage(),
                code: 500
            );
        }
    }
    public function uploadAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:20408',
        ]);

        try {
            $user = auth()->user();
            if (!$user) {
                return $this->sendError('User not found', 404);
            }

            if ($request->hasFile('avatar')) {
                $file = $request->file('avatar');
                $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();

                // Delete old avatar if exists
                if ($user->avatar && file_exists(public_path($user->avatar))) {
                    unlink(public_path($user->avatar));
                }

                // Upload new avatar
                $avatarPath = 'user/avatar/' . $fileName;
                $file->move(public_path('user/avatar'), $fileName);
                $user->avatar = $avatarPath;
                $user->save();
            }

            return $this->sendResponse([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
                'is_verified' => $user->is_verified,
            ], 'Avatar updated successfully.');
        } catch (Exception $e) {
            Log::error("Avatar Upload Error: " . $e->getMessage());
            return $this->sendError('Failed to update profile: ' . $e->getMessage(), 500);
        }
    }
    public function updatePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        try {
            $user = auth()->user();
            if (!$user) {
                return $this->sendError('User not found', 404);
            }

            if (!password_verify($request->old_password, $user->password)) {
                return $this->sendError('Old password is incorrect', 422);
            }

            $user->password = bcrypt($request->new_password);
            $user->save();

            return $this->sendResponse([], 'Password updated successfully.');
        } catch (Exception $e) {
            Log::error('Password update failed: ' . $e->getMessage());
            return $this->sendError('Failed to update password', 500);
        }
    }
    public function deleteAvatar()
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return $this->sendError('User not found', 404);
            }
            // Delete old avatar if exists
            if ($user->avatar && file_exists(public_path($user->avatar))) {
                unlink(public_path($user->avatar));
                Helper::fileDelete($user->avatar);
            }
            // Remove avatar path from user
            $user->avatar = null;
            $user->save();
            return $this->sendResponse([], 'Avatar deleted successfully.');
        } catch (Exception $e) {
            Log::error('Avatar deletion failed: ' . $e->getMessage());
            return $this->sendError('Failed to delete avatar', 500);
        }
    }
    public function deleteAccount(Request $request)
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return $this->sendError('User not found', 404);
            }
            // Delete user account
            $user->delete();
            return $this->sendResponse([], 'Account deleted successfully.');
        } catch (Exception $e) {
            Log::error('Account deletion failed: ' . $e->getMessage());
            return $this->sendError('Failed to delete account', 500);
        }
    }
}
