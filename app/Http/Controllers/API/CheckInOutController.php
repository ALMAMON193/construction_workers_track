<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Traits\ResponseTrait;
use App\Models\EmployeeChecking;
use App\Http\Controllers\Controller;

class CheckInOutController extends Controller
{
    use ResponseTrait;

    public function storeAttendance(Request $request)
{
    $request->validate([
        'time' => 'required|string',
        'role' => 'required|string',
    ]);

    $user = auth()->user();
    if (!$user) {
        return $this->sendError('Unauthorized', 401);
    }

    $isCheckingIn = $request->has('check_in') || $request->input('status') === 'check_in';
    $status = $isCheckingIn ? 'check_in' : 'check_out';

    // For check-out: Find the most recent check-in without a check-out
    if (!$isCheckingIn) {
        $lastCheckIn = EmployeeChecking::where('user_id', $user->id)
            ->whereDate('date', now()->format('Y-m-d'))
            ->where('status', 'check_in')
            ->whereDoesntHave('checkOutPair') // Assuming you have this relationship
            ->latest()
            ->first();

        if (!$lastCheckIn) {
            return $this->sendError('No active check-in found to pair with this check-out', 400);
        }
    }

    $attendanceData = [
        'role' => $request->role,
        'status' => $status,
        'date' => now()->format('Y-m-d'),
        'user_id' => $user->id,
        $isCheckingIn ? 'check_in' : 'check_out' => $request->time
    ];

    $attendance = EmployeeChecking::create($attendanceData);

    // If checking out, calculate and update total hours
    if (!$isCheckingIn && isset($lastCheckIn)) {
        $checkInTime = \Carbon\Carbon::parse($lastCheckIn->check_in);
        $checkOutTime = \Carbon\Carbon::parse($request->time);
        $totalHours = $checkOutTime->diffInHours($checkInTime);

        // Update either the check-out record or check-in record
        $attendance->update(['total_hours' => $totalHours]);
        // OR: $lastCheckIn->update(['check_out' => $request->time, 'total_hours' => $totalHours]);
    }

    $message = $isCheckingIn ? 'Checked in successfully' : 'Checked out successfully';
    return $this->sendResponse($attendance, $message);
}
}
