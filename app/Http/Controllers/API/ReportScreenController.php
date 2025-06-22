<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use App\Models\Earning;
use App\Models\ExpenseMoney;
use Illuminate\Http\Request;
use App\Traits\ResponseTrait;
use App\Models\EmployeeChecking;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ReportScreenController extends Controller
{
    use ResponseTrait;
    public function reportScreen()
    {
        $user = Auth::user();

        $totalExpense = ExpenseMoney::where('user_id', $user->id)->sum('amount_spent');

        // Calculate total duty time
        $totalHours = EmployeeChecking::where('user_id', $user->id)
            ->whereNotNull('total_hours')
            ->sum('total_hours');

        // Convert total hours to minutes
        $totalMinutes = $totalHours * 60;

        // Convert minutes to years, days, and remaining minutes
        $minutesPerYear = 365 * 24 * 60; // Minutes in a year (ignoring leap years for simplicity)
        $minutesPerDay = 24 * 60; // Minutes in a day

        $years = floor($totalMinutes / $minutesPerYear);
        $remainingMinutesAfterYears = $totalMinutes % $minutesPerYear;
        $days = floor($remainingMinutesAfterYears / $minutesPerDay);
        $remainingMinutes = $remainingMinutesAfterYears % $minutesPerDay;

        $totalDutyTime = [
            'years' => $years,
            'days' => $days,
            'minutes' => $remainingMinutes
        ];

        $expense_history = ExpenseMoney::where('user_id', $user->id)->orderBy('date', 'desc')->get();

        // Earning history
        $workersHistory = Earning::where('user_id', $user->id)->with('employeeChecking')
            ->orderBy('earning_date', 'desc')
            ->get();

        return $this->sendResponse([
            'total_earning' => intval($user->total_sallary_amount),
            'total_expense' => $totalExpense,
            'total_duty_time' => $totalDutyTime,
            'chacking_history' => $workersHistory,
            'expense_history' => $expense_history,
        ], 'Report Screen Data');
    }
    public function paychecks($id)
    {
        $user = Auth::user();
        $paychecks = Earning::where('user_id', $user->id)->with('employeeChecking')
            ->orderBy('earning_date', 'desc')
            ->findOrFail($id);
        return $this->sendResponse($paychecks, 'Paychecks Details');
    }
}
