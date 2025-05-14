<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Rating;
use Illuminate\Http\Request;
use App\Traits\ResponseTrait;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class RattingController extends Controller
{
    use ResponseTrait;
    public function ratting(Request $request)
    {
        $request->validate([
            'rating' => 'required|in:1,2,3,4,5',
            'review' => 'required|string',
        ]);

        try {
            $ratting = Rating::where('user_id', Auth::user()->id)->first();
            if ($ratting) {
                $ratting->rating = $request->rating;
                $ratting->review = $request->review;
                $ratting->save();
            } else {
                $ratting = new Rating();
                $ratting->user_id = Auth::user()->id;
                $ratting->rating = $request->rating;
                $ratting->review = $request->review;
                $ratting->save();
            }
            return $this->sendResponse($ratting, 'Ratting added successfully');
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }
    }
}
