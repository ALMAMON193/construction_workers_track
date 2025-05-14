<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Models\FacingProblem;
use App\Traits\ResponseTrait;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class FacingProblemController extends Controller
{
    use ResponseTrait;
    public function index() {
        $facingProblems = FacingProblem::all();
        return $this->sendResponse($facingProblems, 'Facing problems fetched successfully');
    }

    public function store(Request $request)
    {
        $request->validate([
            'description' => 'required',
            'date' => 'required|date',
            'location' => 'required',
        ]);
        $facingProblem = new FacingProblem();
        $facingProblem->description = $request->description;
        $facingProblem->date = date('Y-m-d', strtotime($request->date));
        $facingProblem->location = $request->location;
        $facingProblem->user_id = Auth::user()->id;
        $facingProblem->status = 'pending';
        $facingProblem->save();

        unset($facingProblem->created_at);
        unset($facingProblem->updated_at);
        return $this->sendResponse($facingProblem, 'Facing problem created successfully');
    }
    public function view($id){
        $facingProblem = FacingProblem::find($id);
        if(!$facingProblem){
            return $this->sendError('Facing problem not found');
        }
        unset($facingProblem->created_at);
        unset($facingProblem->updated_at);
        return $this->sendResponse($facingProblem, 'Facing problem fetched successfully');
    }
}
