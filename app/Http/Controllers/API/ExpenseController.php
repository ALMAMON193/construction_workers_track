<?php

namespace App\Http\Controllers\API;

use App\Helpers\Helper;
use Illuminate\Support\Str;
use App\Models\ExpenseMoney;
use Illuminate\Http\Request;
use App\Traits\ResponseTrait;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ExpenseController extends Controller
{
    use ResponseTrait;

    public function index(){
        $expenses = ExpenseMoney::where('user_id', Auth::user()->id)->get();
        return $this->sendResponse($expenses, 'All Expenses');
    }
    public function store(Request $request){
        // dd($request->all());
        // $request->validate
        $request->validate([
            'amount_spent' => 'required|numeric',
            'date' => 'required|date',
            'category' => 'required|string',
            'payment_method' => 'required|string',
            'location' => 'required|string',
            'description' => 'required|string',
            'file' => 'required|file|mimes:jpeg,png,jpg,gif|max:10240',
        ]);

        //file upload
        $fileUrl = '';
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileUrl = Helper::fileUpload($file, 'expense');
        }
        //store in database
        $expense = new ExpenseMoney();
        $expense->amount_spent = $request->amount_spent;
        $expense->date = $request->date;
        $expense->category = $request->category;
        $expense->payment_method = $request->payment_method;
        $expense->location = $request->location;
        $expense->description = $request->description;
        $expense->user_id = Auth::user()->id;
        $expense->file = $fileUrl;
        $expense->save();

        return $this->sendResponse($expense, 'Expense Money added successfully.');

    }
    public function view($id){
        $expense = ExpenseMoney::find($id);
        if(!$expense){
            return $this->sendError('Expense not found');
        }
        return $this->sendResponse($expense, 'Your Expense Money Details');
    }
}
