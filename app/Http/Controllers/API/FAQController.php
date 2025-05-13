<?php

namespace App\Http\Controllers\API;

use Exception;

use App\Models\FAQ;
use App\Traits\ResponseTrait;
use App\Http\Controllers\Controller;

class FAQController extends Controller
{
    use ResponseTrait;
    public function index()
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return $this->sendError('Unauthorized', [], 401);
            }
            $faqs = FAQ::all();
            return $this->sendResponse($faqs, 'Faq retrieved successfully');
        } catch (Exception $e) {
            return $this->sendError(
                'Something went wrong',
                500
            );
        }
    }
}
