<?php

namespace App\Http\Controllers\API;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Earning;
use Illuminate\Http\Request;
use App\Traits\ResponseTrait;
use App\Models\EmployeeChecking;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class CheckInOutController extends Controller
{
    use ResponseTrait;
    public function storeAttendance(Request $request)
    {
        $request->validate([
            'time' => 'required|string',
            'current_location' => 'nullable|string',
            'role' => 'required|string',
            'long' => 'nullable|numeric',
            'lat' => 'nullable|numeric',
            'status' => 'nullable|in:check_in,check_out',
        ]);

        $user = auth()->user();
        if (!$user) {
            return $this->sendError('Unauthorized', 401);
        }

        $isCheckingIn = $request->has('check_in') || $request->input('status') === 'check_in';
        $currentDate = now()->format('Y-m-d');

        if ($isCheckingIn) {
            return $this->handleCheckIn($user, $request, $currentDate);
        }
        return $this->handleCheckOut($user, $request, $currentDate);
    }

    protected function handleCheckIn($user, $request, $date)
    {

        $hasActiveCheckIn = EmployeeChecking::where('user_id', $user->id)
            ->whereDate('date', $date)
            ->whereNull('check_out')
            ->exists();

        if ($hasActiveCheckIn) {
            return $this->sendError('You have an active check-in. Please check-out first.', 400);
        }
        $attendance = EmployeeChecking::create([
            'user_id' => $user->id,
            'role' => $request->role,
            'current_location' => $request->current_location,
            'status' => 'check_in',
            'lat' => $request->lat,
            'long' => $request->long,
            'date' => $date,
            'check_in' => $request->time,
            'check_out' => null,
            'total_hours' => null,
        ]);

        return $this->sendResponse($attendance, 'Checked in successfully');
    }

    protected function handleCheckOut($user, $request, $date)
    {
        $lastCheckIn = EmployeeChecking::where('user_id', $user->id)
            ->whereDate('date', $date)
            ->whereNull('check_out')
            ->latest()
            ->first();

        if (!$lastCheckIn) {
            return $this->sendError('No active check-in found to pair with this check-out', 400);
        }
        //auth user details
        $user = auth()->user();

        $checkInTime = Carbon::parse($lastCheckIn->check_in);
        $checkOutTime = Carbon::parse($request->time);
        $diff = $checkOutTime->diff($checkInTime);

        $totalHoursDecimal = $diff->h + ($diff->i / 60);
        $totalHoursFormatted = $diff->h . ' Hours ' . $diff->i . ' min';

        $lastCheckIn->update([
            'check_out' => $request->time,
            'total_hours' => $totalHoursFormatted,
            'status' => 'check_out',
            'current_location' => $request->current_location
        ]);
        $role = $lastCheckIn->role;

        $hourlyRate = $user->hourly_working_rate;
        $vatPercentage = $user->hourly_working_rate_vat;

        // Calculate gross salary
        $grossSalary = $totalHoursDecimal * $hourlyRate;

        // Calculate VAT amount to deduct
        $vatAmount = $grossSalary * ($vatPercentage / 100);

        // Calculate net salary
        $netSalary = $grossSalary - $vatAmount;

        Earning::create([
            'user_id' => $user->id,
            'employee_checking_id' => $lastCheckIn->id,
            'earning_date' => $date,
            'role' => $role,
            'salary' => $grossSalary,
            'working_hours' => $totalHoursFormatted,
            'vat' => $vatAmount,
            'total_salary' => $netSalary
        ]);

        // Update user's total salary amount
        $user->increment('total_sallary_amount', $netSalary);
        DB::commit();
        $this->updateUserTotalDutyTime($user);

        return $this->sendResponse([
            'attendance' => $lastCheckIn,
            'earnings' => [
                'role' => (string)$role,
                'check_in' => (string)$lastCheckIn->check_in,
                'hourly_rate' => (float)$hourlyRate,
                'hours_worked' => (string)$totalHoursFormatted,
                'gross_salary' => (float)number_format($grossSalary, 2),
                'tax' => (float)number_format($vatAmount, 2),
                'tax_rate' => (float)$vatPercentage . '%',
                'net_salary' => (float)number_format($netSalary, 2)
            ]
        ], 'Checked out successfully with earnings calculated');
    }

    protected function updateUserTotalDutyTime($user)
    {
        // Count unique days with at least one check-out
        $workingDays = EmployeeChecking::where('user_id', $user->id)
            ->whereNotNull('check_out')
            ->distinct('date')
            ->count('date');

        // Log for debugging
        Log::info("Calculated unique working days for user {$user->id}: {$workingDays}");
        try {
            $user->update(['working_days' => $workingDays]);
        } catch (Exception $e) {
            Log::error("Failed to update working_days for user {$user->id}: {$e->getMessage()}");
        }
    }
    public function todayAttendance(Request $request)
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return $this->sendError('Unauthorized', 401);
            }
            $today = now()->format('Y-m-d');
            $attendance = EmployeeChecking::where('user_id', $user->id)->whereDate('date', $today)->get();
            return $this->sendResponse($attendance, 'Today Attendance');
        } catch (Exception $e) {
            return $this->sendError('Something went wrong', 500);
        }
    }

    //checking user checking history
    public function checkingHistory()
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return $this->sendError('Unauthorized', 401);
            }

            $attendance = EmployeeChecking::where('user_id', $user->id)
                ->select('role', 'date', 'check_in', 'check_out', 'total_hours', 'lat', 'long')
                ->orderByDesc('date')
                ->get();

            $data = [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'avatar' => $user->avatar ?? '',
                    'working_days' => intval($user->working_days) ?? "0 Days",
                ],
                'attendance' => $attendance ?? [],
            ];
            return $this->sendResponse($data, 'Checking History');
        } catch (Exception $e) {
            return $this->sendError('Something went wrong', 500);
        }
    }
}
