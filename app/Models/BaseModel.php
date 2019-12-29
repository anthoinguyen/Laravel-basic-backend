<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Validator;

class BaseModel extends Model
{
  public static function validate($data = array(), $ruleName = '', $message = array())
  {
    $rules = static::$rules[$ruleName];
    $validator = Validator::make($data, $rules, $message);
    return self::getErrors($validator);
  }

  private static function getErrors($validator)
  {
    $errors = Lang::get('errorCodes');
    $errorList = array();
    foreach ($validator->errors()->all() as $error) {
      $message = $errors['apiErrorMessages'][$error];
      $code = $errors['apiErrorCodes'][$error];
      $errorList[] = array($code, $message);
    }
    return $errorList;
  }
}
