<?php

namespace App\Traits;

trait ApiResponse
{
    protected function successResponse($data = null, $message = "Success", $status = 200)
    {
        return response()->json([
            "success" => true,
            "message" => $message,
            "data" => $data,
            "errors" => null
        ], $status);
    }

    protected function errorResponse($message = "Error", $errors = null, $status = 400)
    {
        return response()->json([
            "success" => false,
            "message" => $message,
            "data" => null,
            "errors" => $errors
        ], $status);
    }
}