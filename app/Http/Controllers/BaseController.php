<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;

class BaseController extends Controller
{
  protected $statusCode = 200;
  protected $error;

  public function __construct(Request $request)
  {
    $this->request = $request;
    $this->error = Lang::get('errorCodes');
  }

  private function setStatusCode($statusCode)
  {
    $this->statusCode = $statusCode;
    return $this;
  }

  public function respondSuccess($data = array())
  {
    return response()->json(array(
      'error' => false,
      'data' => $data,
      'errors' => null
    ), $this->statusCode);
  }

  public function BadRequest($errors)
  {
    $this->setStatusCode(400);
    $errorsList = array(
      'errorCode' => $this->error['apiErrorCodes'][$errors],
      'errorMessage' => $this->error['apiErrorMessages'][$errors]
    );

    return response()->json(array(
      'error' => true,
      'data' => null,
      'errors' => $errorsList
    ), $this->statusCode);
  }

  public function Unauthorized($errors)
  {
    $this->setStatusCode(401);

    $errorsList = array(
      'errorCode' => $this->error['apiErrorCodes'][$errors],
      'errorMessage' => $this->error['apiErrorMessages'][$errors]
    );

    return response()->json(array(
      'error' => true,
      'data' => null,
      'errors' => $errorsList
    ), $this->statusCode);
  }

  public function respondWithError($errors, $statusCode = null)
  {
    if (empty($statusCode)) {
      $this->setStatusCode(400);
    } else {
      $this->setStatusCode($statusCode);
    }

    $errorsList = array();
    foreach ($errors as $error) {
      $errorsList[] = array(
        'errorCode' => (int) $error[0],
        'errorMessage' => $error[1],
      );
    }

    return response()->json(array(
      'error' => true,
      'data' => null,
      'errors' => $errorsList
    ), $this->statusCode);
  }

  public function respondWithCustomError($errors, $statusCode = null)
  {
    if (empty($statusCode)) {
      $this->setStatusCode(400);
    } else {
      $this->setStatusCode($statusCode);
    }

    $errorsList = array(
      'errorCode' => $this->error['apiErrorCodes'][$errors],
      'errorMessage' => $this->error['apiErrorMessages'][$errors]
    );

    return response()->json(array(
      'error' => true,
      'data' => null,
      'errors' => [$errorsList]
    ), $this->statusCode);
  }

  public function InternalServerError($message = '')
  {
    $this->setStatusCode(500);
    return response()->json(array(
      'error' => true,
      'data' => null,
      'errors' => [array(
        'errorCode' => 9999,
        'errorMessage' => $message
      )]
    ), $this->statusCode);
  }
}
