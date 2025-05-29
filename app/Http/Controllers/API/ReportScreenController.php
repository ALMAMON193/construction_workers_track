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

        $totalExpense = ExpenseMoney::where('user_id', $user->id)->sum('amount_spent');
        $totalDutyTime = (int) $user->working_days;
        $years = floor($totalDutyTime / (60 * 24 * 365));
        $days = floor(($totalDutyTime % (60 * 24 * 365)) / (60 * 24));
        $hours = floor(($totalDutyTime % (60 * 24)) / 60);
        $minutes = $totalDutyTime % 60;
        $totalDutyTime = "$years years $days days $hours hours $minutes minutes";
        $expense_history = ExpenseMoney::where('user_id', $user->id)->orderBy('date', 'desc')->get();


        //earning history
        $workersHistory = Earning::where('user_id', $user->id)->with('employeeChecking')
            ->orderBy('earning_date', 'desc')
            ->get();
        return $this->sendResponse([
            'total_earning' => $user->total_sallary_amount,
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
