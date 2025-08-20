<?php

namespace App\Traits;

trait ApiResponseTrait
{
    protected function successResponse($data = [], $message = 'Success', $statusCode = 200)
    {
        return response()->json([
            'status' => true,
            'status_code' => $statusCode,
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }

    protected function errorResponse($message = 'Error', $statusCode = 400, $errors = [])
    {
        return response()->json([
            'status' => false,
            'status_code' => $statusCode,
            'message' => $message,
            'errors' => $errors
        ], $statusCode);
    }
}
