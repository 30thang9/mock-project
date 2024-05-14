<?php

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

if (!function_exists('apiResponse')) {
    /**
     * Generate an API response.
     *
     * @param string $message
     * @param mixed|null $data
     * @param int $statusCode
     * @return JsonResponse
     */
    function apiResponse(string $message, mixed $data = null, int $statusCode = ResponseAlias::HTTP_OK): JsonResponse
    {
        $success = $statusCode >= ResponseAlias::HTTP_OK && $statusCode < ResponseAlias::HTTP_MULTIPLE_CHOICES;

        return response()->json([
            'status' => $success ?'success' : 'failure',
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }
}
