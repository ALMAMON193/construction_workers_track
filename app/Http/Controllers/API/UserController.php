<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Traits\ResponseTrait;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    use ResponseTrait;
    public function UserLocation()
    {
        $user = auth()->user();
        if (!$user) {
            return $this->sendError('Unauthorized', [], 401);
        }
        $user_location = $user->userLocations;
        return $this->sendResponse($user_location, 'User Location retrieved successfully');
    }
    public function UserLocationStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'lat' => 'required|numeric',  // Changed from 'float' to 'numeric'
            'long' => 'required|numeric', // Changed from 'float' to 'numeric'
            'building' => 'required|string',
            'appointment' => 'nullable|string',
            'floor' => 'nullable|string',
            'category' => 'nullable|in:Home,Office,Other', // Added enum validation
        ]);

        $user = auth()->user();
        if (!$user) {
            return $this->sendError('Unauthorized', [], 401);
        }

        $user_location = $user->userLocations()->create([
            'name' => $request->name,
            'user_id' => $user->id,
            'lat' => (float) $request->lat,  // Explicitly cast to float
            'long' => (float) $request->long, // Explicitly cast to float
            'building' => $request->building,
            'appointment' => $request->appointment ?? '',
            'floor' => $request->floor ?? '',
            'category' => $request->category ?? '',
        ]);

        return $this->sendResponse($user_location, 'User Location created successfully');
    }

    public function UserLocationUpdate($id, Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'lat' => 'required|numeric',
            'long' => 'required|numeric',
            'building' => 'required|string',
            'appointment' => 'nullable|string',
            'floor' => 'nullable|string',
            'category' => 'nullable|in:Home,Office,Other',
        ]);

        $user = auth()->user();
        if (!$user) {
            return $this->sendError('Unauthorized', [], 401);
        }

        $user_location = $user->userLocations()->where('id', $id)->first();
        if (!$user_location) {
            return $this->sendError('User Location not found', [], 404);
        }

        $user_location->update([
            'name' => $request->name,
            'lat' => (float) $request->lat,
            'long' => (float) $request->long,
            'building' => $request->building,
            'appointment' => $request->appointment ?? '',
            'floor' => $request->floor ?? '',
            'category' => $request->category ?? '',
        ]);

        return $this->sendResponse($user_location, 'User Location updated successfully');
    }
}
