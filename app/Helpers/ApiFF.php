<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;

class ApiFF
{
  /**
   * Return a standardized API JSON response.
   *
   * @param  string       $status   “success” or “error”
   * @param  string       $message  Human-readable message
   * @param  mixed|null   $payload  Data to return (or null)
   * @param  int          $code     HTTP status code (default 200)
   * @return JsonResponse
   */
  private static function api_response(
    string $status,
    string $message,
    $payload = null,
    int $code = 200
  ): JsonResponse {
    return response()->json([
      'status'   => $status,
      'message' => $message,
      'payload'  => $payload,
    ], $code);
  }

  /**
   * Shorthand for a 200-OK “success” response.
   */
  public static function api_success(
    string $message,
    $payload = null,
    int $code = 200
  ): JsonResponse {
    return self::api_response('success', $message, $payload, $code);
  }

  /**
   * Shorthand for an error (defaults to 400 Bad Request).
   */
  public static function api_error(
    string $message,
    $payload = null,
    int $code = 400
  ): JsonResponse {
    return self::api_response('error', $message, $payload, $code);
  }
}
