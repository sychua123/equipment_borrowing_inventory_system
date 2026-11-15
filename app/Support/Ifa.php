<?php
// Author: Lai Chee Kit
// Module 4: builds web service responses in the team's Interface Agreement (IFA) format.
//
// Every response has:  status ("S" success, "F" failed request, "E" server error),
//                      requestID (copied from the request), timeStamp, and message or data.

namespace App\Support;

use Illuminate\Http\JsonResponse;

class Ifa
{
    public const TIME_FORMAT = 'Y-m-d H:i:s';

    public static function success(?string $requestId, array $data): JsonResponse
    {
        return response()->json([
            'status' => 'S',
            'requestID' => $requestId,
            'timeStamp' => now()->format(self::TIME_FORMAT),
            'data' => $data,
        ]);
    }

    // The caller sent something wrong (bad key, missing field, unknown ID)
    public static function fail(mixed $requestId, string $message, int $httpStatus = 422, array $errors = []): JsonResponse
    {
        return self::problem('F', $requestId, $message, $httpStatus, $errors);
    }

    // Something broke on our side
    public static function error(mixed $requestId, string $message = 'The service is temporarily unavailable.'): JsonResponse
    {
        return self::problem('E', $requestId, $message, 500);
    }

    private static function problem(string $status, mixed $requestId, string $message, int $httpStatus, array $errors = []): JsonResponse
    {
        $body = [
            'status' => $status,
            'requestID' => is_string($requestId) ? $requestId : null,
            'timeStamp' => now()->format(self::TIME_FORMAT),
            'message' => $message,
        ];

        if ($errors !== []) {
            $body['errors'] = $errors;
        }

        return response()->json($body, $httpStatus);
    }
}
