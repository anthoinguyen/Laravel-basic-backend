<?php

namespace App\Http\Controllers;

use App\User;
use Exception;
use JWTAuth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\PasswordReset;
use App\Notifications\ResetPasswordRequest;

/**
 * @SWG\Swagger(
 *     basePath="/api",
 *     schemes={"http", "https"},
 *     host="localhost/laravel-basic-backend/public",
 *     @SWG\Info(
 *         version="1.0.0", title="Laravel Basic API", description="L5 Swagger API with Laravel basic",
 *         @SWG\Contact(
 *             email= ""
 *         ),
 *     ),
 * )
 */

class AuthController extends BaseController
{

  public function __construct(Request $request)
  {
    $this->request = $request;
    $this->error = Lang::get('errorCodes');
    parent::__construct($request);
  }

  /**
   * @SWG\POST(
   *      path="/auth/register",
   *      operationId="register",
   *      tags={"Auth"},
   *      summary="Create a new user",
   *      description="",
   *      @SWG\Parameter(
   *          name="body", description="Create a user", required=true, in="body",
   *          @SWG\Schema(
   *              @SWG\Property( property="email", type="string", default="admin_123@gmail.com"),
   *              @SWG\Property( property="phone", type="string", default="84 123456789"),
   *              @SWG\Property( property="password", type="string", default="123456"),
   *              @SWG\Property( property="name", type="string", default="admin 123"),
   *              @SWG\Property( property="gender", type="boolean", default=false),
   *              @SWG\Property( property="dateOfBirth", type="string", default="10/10/2010"),
   *              @SWG\property( property="about", type="string", default="T la admin")
   *          ),
   *      ),
   *      @SWG\Response(response=200, description="Register Successfully"),
   *      @SWG\Response(response=400, description="Bad Request"),
   *      @SWG\Response(response=500, description="Internal Server Error"),
   * )
   *
   */

  public function register(Request $request)
  {
    try {
      $input = $request->all();
      $validatorError = User::validate($input, 'RULE_REGISTER');

      if (!empty($validatorError)) {
        return $this->respondWithError($validatorError);
      }

      // TODO: Why not define dob model for user to follow up ??
      $strBirthDate = explode('/', $request->dateOfBirth);
      $birthDay = $strBirthDate[2] . '-' . $strBirthDate[1] . '-' . $strBirthDate[0];

      $user = new User();
      $user->name = $request->name;
      $user->email = $request->email;
      $user->phone = $request->phone;
      $user->password = bcrypt($request->password);
      $user->gender = (($request->gender === true) ? 1 : 0);
      $user->date_of_birth = $birthDay;
      $user->about = $request->about;
      $user->save();

      $token = JWTAuth::fromUser($user);

      $result = [
        'user' => $user,
        'token' => $token
      ];
      return $this->respondSuccess($result);
    } catch (Exception $e) {
      return $this->InternalServerError($e->getMessage());
    }
  }

  /**
   * @SWG\POST(
   *      path="/auth/login",
   *      operationId="Login",
   *      tags={"Auth"},
   *      summary="Login",
   *      description="",
   *      @SWG\Parameter(
   *          name="body", description="user login", required=true, in="body",
   *          @SWG\Schema(
   *              @SWG\Property(property="emailOrPhone", type="string", default="admin_123@gmail.com"),
   *              @SWG\Property(property="password", type="string", default="123456"),
   *          ),
   *      ),
   *      @SWG\Response(response=200, description="Login Successfully"),
   *      @SWG\Response(response="400", description="Bad Request"),
   *      @SWG\Response(response="422", description="Unprocessable Entity"),
   *      @SWG\Response(response="500", description="Internal Server Error"),
   * )
   */

  public function login(Request $request)
  {
    try {
      $login = $request->emailOrPhone;
      $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
      $request->merge([$field => $login]);

      $input = $request->all();
      $validatorError = User::validate($input, 'RULE_LOGIN');

      if (!empty($validatorError)) {
        return $this->respondWithError($validatorError);
      }

      $credentials = $request->only($field, 'password');
      $token = JWTAuth::attempt($credentials);
      if (!$token) {
        return $this->respondWithCustomError('tokens_invalid_credentials', 400);
      }

      return $this->respondSuccess($token);
    } catch (Exception $e) {
      return $this->InternalServerError($e->getMessage());
    }
  }

  /**
   * @SWG\POST(
   *      path="/auth/request-reset-password",
   *      operationId="ResetPassword",
   *      tags={"Auth"},
   *      summary="Request Reset Password",
   *      description="Email Request Reset Password",
   *      @SWG\Parameter(
   *          name="body", description="", required=true, in="body",
   *          @SWG\Schema(
   *              @SWG\Property(property="email", type="string", default="admin_123@gmail.com"),
   *          ),
   *      ),
   *      @SWG\Response(response=200, description="Request Reset Password Successfully"),
   *      @SWG\Response(response="400", description="Bad Request"),
   *      @SWG\Response(response="422", description="Unprocessable Entity"),
   *      @SWG\Response(response="500", description="Internal Server Error"),
   * )
   *
   */

  public function requestResetPassword(Request $request)
  {
    try {
      $input = $request->all();
      $validatorError = User::validate($input, 'RULE_REQUEST_RESET_PASSWORD');

      if (!empty($validatorError)) {
        return $this->respondWithError($validatorError);
      }

      $user = User::where('email', $request->email)->first();
      if (!$user) {
        return $this->respondWithCustomError('users_not_found', 401);
      }

      $passwordReset = PasswordReset::updateOrCreate([
        'email' => $user->email,
      ], [
        'token' => Str::random(60),
        'expired_at' => Carbon::now()->addMinutes(3),
      ]);

      if ($passwordReset && $user) {
        $user->notify(new ResetPasswordRequest($passwordReset->token));
      }

      return $this->respondSuccess($passwordReset);
    } catch (Exception $e) {
      return $this->InternalServerError($e->getMessage());
    }
  }

  /**
   * @SWG\POST(
   *      path="/auth/accept-reset-password",
   *      operationId="postTokenResetPassword",
   *      tags={"Auth"},
   *      summary="Accept Reset Password",
   *      description="Accept Token Reset Password",
   *      @SWG\Parameter(
   *          name="body", description="input password to reset", required=true, in="body",
   *          @SWG\Schema(
   *              @SWG\Property(property="token", type="string", default="123456"),
   *              @SWG\Property(property="password", type="string", default="123456"),
   *              @SWG\Property(property="confirmPassword", type="string", default="123456"),
   *          ),
   *      ),
   *      @SWG\Response(response=200, description="Reset Password Successfully"),
   *      @SWG\Response(response="400", description="Bad Request"),
   *      @SWG\Response(response="422", description="Unprocessable Entity"),
   *      @SWG\Response(response="500", description="Internal Server Error"),
   * )
   *
   */

  public function acceptResetPassword(Request $request)
  {
    try {
      $input = $request->all();
      $validatorError = User::validate($input, 'RULE_ACCEPT_RESET_PASSWORD');

      if (!empty($validatorError)) {
        return $this->respondWithError($validatorError);
      }

      $passwordReset = PasswordReset::where('token', $request->token)->first();
      if (!$passwordReset) {
        return $this->respondWithCustomError('tokens_invalid', 401);
      }
      if (Carbon::parse($passwordReset->expired_at)->isPast()) {
        $passwordReset->delete();
        return $this->respondWithCustomError('tokens_expired', 401);
      }

      $user = User::where('email', $passwordReset->email)->first();
      if (!$user) {
        return $this->respondWithCustomError('users_not_found', 401);
      }

      $user->password = bcrypt($request->password);
      $user->save();
      $passwordReset->delete();

      return $this->respondSuccess($user);
    } catch (Exception $e) {
      return $this->InternalServerError($e->getMessage());
    }
  }


  /**
   * @SWG\PUT(
   *      path="/change-password",
   *      operationId="ChangePassword",
   *      tags={"Auth"},
   *      summary="Change Password",
   *      security={{"jwt":{}}},
   *      description="",
   *       @SWG\Parameter(
   *          name="body", description="", required=true, in="body",
   *          @SWG\Schema(
   *              @SWG\Property(property="currentPassword", type="string", default="123456789"),
   *              @SWG\Property(property="newPassword", type="string", default="123456"),
   *              @SWG\Property(property="confirmNewPassword", type="string", default="123456"),
   *          ),
   *      ),
   *      @SWG\Response(response=200, description="Change Password Successfully"),
   *      @SWG\Response(response="400", description="Bad Request"),
   *      @SWG\Response(response="422", description="Unprocessable Entity"),
   *      @SWG\Response(response="500", description="Internal Server Error"),
   * )
   *
   */

  public function changePassword(Request $request)
  {
    try {
      $input = $request->all();
      $validatorError = User::validate($input, 'RULE_CHANGE_PASSWORD');

      if (!empty($validatorError)) {
        return $this->respondWithError($validatorError);
      }

      $currentPassword = $request->currentPassword;
      $newPassword = $request->newPassword;

      $user = $request->userData;
      if (!$user) {
        return $this->respondWithCustomError('users_not_found', 401);
      }
      if (!Hash::check($currentPassword, $user->password)) {
        return $this->respondWithCustomError('users_currentPassword_incorrect', 401);
      } else {
        $user->password = bcrypt($newPassword);
        $user->save();
      }

      return $this->respondSuccess($user);
    } catch (Exception $e) {
      return $this->InternalServerError($e->getMessage());
    }
  }
}
