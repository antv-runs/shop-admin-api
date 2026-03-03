<?php

namespace App\Traits;

use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Resources\Json\ResourceCollection;

trait HttpResponses
{
    protected function success($data = null, string $message = null, int $code = Response::HTTP_OK)
    {
        // Case 1: Resource collection (usually paginated list)
        if ($data instanceof ResourceCollection) {
            return $data->additional([
                'success' => true,
                'message' => $message,
            ]);
        }

        $response = [
            'success' => true,
        ];

        if (!is_null($message)) {
            $response['message'] = $message;
        }

        if (!is_null($data)) {
            $response['data'] = $data;
        }

        // Case 2: Single object or simple data
        return response()->json($response, $code);
    }

    protected function error(string $message = null, int $code = Response::HTTP_BAD_REQUEST, $errors = null)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], $code);
    }
}
