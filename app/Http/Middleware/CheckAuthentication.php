<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Carbon\Carbon;

class CheckAuthentication
{
  /**
   * Handle an incoming request.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \Closure  $next
   * @return mixed
   */


  public function handle($request, Closure $next)
  {
    try {
        // TODO: Why not using JWTAuth ????
      $token = str_replace('Bearer ', "", $request->header('Authorization'));
      $tokenstr = explode('.', $token);
      $headerEncoded = $tokenstr[0];

      $payloadEncoded = $tokenstr[1];
      $base64urlPayload = strtr($payloadEncoded, '-_,', '+/=');
      $payload = base64_decode($base64urlPayload);
      $payloadJson = json_decode($payload);

      $signatureEncoded = $tokenstr[2];
      $base64urlSign = strtr($signatureEncoded, '-_,', '+/=');
      $signature = base64_decode($base64urlSign);

      $dataEncoded = "$headerEncoded.$payloadEncoded";

      $rawSignature = hash_hmac('SHA256', $dataEncoded, env('JWT_SECRET'), true);
      $verify = hash_equals($rawSignature, $signature);

      if ($verify === false) {
        return response()->json([
          'error' => true,
          'data' => null,
          'errors' => [
            'errorCode' => '1004',
            'errorMessage' => 'Authorization Token not found'
          ]
        ], 401);
      }
      if (Carbon::createFromTimestamp($payloadJson->exp)->isPast()) {
        return response()->json([
          'error' => true,
          'data' => null,
          'errors' => [
            'errorCode' => '1006',
            'errorMessage' => 'Token is Expired'
          ]
        ], 401);
      }
    } catch (Exception $e) {
      return response()->json([
        'error' => true,
        'data' => null,
        'errors' => [
          'errorCode' => '1100',
          'errorMessage' => $e->getMessage(),
        ],
      ]);
    }


    $request->userId = $payloadJson->sub;
    return $next($request);
  }
}
