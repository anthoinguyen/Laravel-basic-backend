<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Exception;

class UserController extends BaseController
{
  public function __construct(Request $request)
  {
    $this->request = $request;
    $this->error = Lang::get('errorCodes');
    parent::__construct($request);
  }
  /**
   * @SWG\GET(
   *      path="/user",
   *      operationId="GetProfileUser",
   *      tags={"Users"},
   *      summary="Get Profile User",
   *      security={{"jwt":{}}},
   *      description="Get Profile User",
   *     
   *      @SWG\Response(response=200, description="Get profile Successfully"),
   *      @SWG\Response(response="400", description="Bad Request"),
   *      @SWG\Response(response="422", description="Unprocessable Entity"),
   *      @SWG\Response(response="500", description="Internal Server Error"),
   * )
   *
   */

  public function getProfileUser(Request $request)
  {
    try {
      $user = $request->userData;
      if (!$user) {
        return $this->respondWithCustomError('users_not_found', 401);
      }

      return $this->respondSuccess($user);
    } catch (Exception $e) {
      return $this->InternalServerError($e->getMessage());
    }
  }

  /**
   * @SWG\PUT(
   *      path="/edit-profile",
   *      operationId="putEditProfile",
   *      tags={"Users"},
   *      summary="Edit Profile User",
   *      security={{"jwt":{}}},
   *      description="Edit Profile User",
   *     
   *       @SWG\Parameter(
   *          name="body", description="Edit profile user", required=true, in="body",
   *          @SWG\Schema(
   *               @SWG\Property(property="name", type="string", default="admin 123"),
   *               @SWG\Property(property="gender", type="boolean", default=false),
   *               @SWG\Property(property="dateOfBirth", type="string", default="10/10/2010"),
   *               @SWG\property(property="about", type="string", default="T la admin")
   *          ),
   *      ),
   *      @SWG\Response(response=200, description="Edit profile successfully"),
   *      @SWG\Response(response="400", description="Bad Request"),
   *      @SWG\Response(response="422", description="Unprocessable Entity"),
   *      @SWG\Response(response="500", description="Internal Server Error"),
   * )
   *
   */

  public function editProfile(Request $request)
  {
    try {
      $input = $request->all();
      $validatorError = User::validate($input, 'RULE_EDIT_PROFILE');

      if (!empty($validatorError)) {
        return $this->respondWithError($validatorError);
      }

      $strBirthDate = explode('/', $request->dateOfBirth);
      $birthDay = $strBirthDate[2] . '-' . $strBirthDate[1] . '-' . $strBirthDate[0];

      $user = $request->userData;
      if (!$user) {
        return $this->respondWithCustomError('users_not_found', 401);
      }

      $user->name = $request->name;
      $user->gender = (($request->gender === true) ? 1 : 0);
      $user->date_of_birth = $birthDay;
      $user->about = $request->about;
      $user->save();

      return $this->respondSuccess($user);
    } catch (Exception $e) {
      return $this->InternalServerError($e->getMessage());
    }
  }
}
