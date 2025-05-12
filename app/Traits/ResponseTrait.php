<?php

namespace App\Traits;

trait ResponseTrait
{
    public function sendResponse($data, $message = '', $error = '', $code = 200): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'error' => $error,
        ], $code);
    }

    public function sendError($error, $code = 400, $data = null)
    {
        return response()->json([
            'success' => false,
            'data' => $data,
            'message' => '',
            'error' => $error,
        ], $code);
    }
}
