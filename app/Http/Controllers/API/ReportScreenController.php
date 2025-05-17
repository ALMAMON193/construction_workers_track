<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use App\Models\Earning;
use App\Models\ExpenseMoney;
use Illuminate\Http\Request;
use App\Traits\ResponseTrait;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ReportScreenController extends Controller
{
    use ResponseTrait;
    public function reportScreen(){
        $user = Auth::user();
        $totoalEarning = Earning::where('user_id', $user->id)->sum('total_salary');
        $totalExpense = ExpenseMoney::where('user_id', $user->id)->sum('amount_spent');
        $totalDutyTime = (int) $user->working_days;
        $years = floor($totalDutyTime / (60 * 24 * 365));
        $days = floor(($totalDutyTime % (60 * 24 * 365)) / (60 * 24));
        $hours = floor(($totalDutyTime % (60 * 24)) / 60);
        $minutes = $totalDutyTime % 60;
        $totalDutyTime = "$years years $days days $hours hours $minutes minutes";

        //earning history
        $workersHistory = Earning::where('user_id', $user->id)->with('employeeChecking')
            ->orderBy('earning_date', 'desc')
            ->get();
        return $this->sendResponse([
            'total_earning' => $totoalEarning,
            'total_expense' => $totalExpense,
            'total_duty_time' => $totalDutyTime,
            'earning_history' => $workersHistory,
        ], 'Report Screen');
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
