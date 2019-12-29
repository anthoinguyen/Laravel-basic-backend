<?php

namespace App\Http\Middleware;

use Closure;
use JWTAuth;
use Exception;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;

class JwtMiddleware extends BaseMiddleware
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
      $user = JWTAuth::parseToken()->authenticate();
      $request->userData = $user;
    } catch (Exception $e) {
      if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException) {
        return response()->json(array(
          'error' => true,
          'data' => null,
          'errors' => [array(
            'errorCode' => 3004,
            'errorMessage' => 'Token is Invalid'
          )],
        ), 401);
      } else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
        return response()->json(array(
          'error' => true,
          'data' => null,
          'errors' => [array(
            'errorCode' => 3002,
            'errorMessage' => 'Token is Expired'
          )],
        ), 401);
      } else {
        return response()->json(array(
          'error' => true,
          'data' => null,
          'errors' => [array(
            'errorCode' => 3000,
            'errorMessage' => 'Token not found'
          )],
        ), 401);
      }
    }

    return $next($request);
  }
}
