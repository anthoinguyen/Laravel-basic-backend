<?php

namespace App;

use Illuminate\Notifications\Notifiable;
// use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Models\BaseModel;
use Illuminate\Auth\Authenticatable;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;

class User extends BaseModel implements AuthenticatableContract, AuthorizableContract, JWTSubject
{
  use Notifiable, Authenticatable, Authorizable;

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
    'name', 'email', 'phone', 'password', 'gender', 'dateOfBirth', 'about'
  ];

  /**
   * The attributes that should be hidden for arrays.
   *
   * @var array
   */
  protected $hidden = [
    'password', 'remember_token',
  ];

  /**
   * The attributes that should be cast to native types.
   *
   * @var array
   */
  protected $casts = [
    'email_verified_at' => 'datetime',
  ];

  public function posts()
  {
    return $this->hasMany('App\Models\Post', 'user_id','id');
  }

  public function friend_request_lists()
  {
    return $this->hasMany('App\Models\FriendRequestList','user_id','id');
  }

  public function friend_lists()
  {
    return $this->hasMany('App\Models\FriendList','user_id','id');
  }

  public function getJWTIdentifier()
  {
    return $this->getKey();
  }

  public function getJWTCustomClaims()
  {
    return [];
  }

  public static $rules = array(
    'RULE_REGISTER' => array(
      'name' => 'required|min:5',
      'email' => 'required|regex:/^[a-z][a-z0-9_\.]{2,}@[a-z0-9]{2,}(\.[a-z]{2,}){1,2}$/|unique:users|string',
      'phone' => 'required|regex:/^[0-9]{2,3}[0-9]{9}$/|max:12',
      'password'  => 'required|min:6|max:16|string',
      'gender' => 'required|boolean',
      'dateOfBirth' => 'required|date_format:d/m/Y',
      'about' => 'required|string|max:100'
    ),
    'RULE_LOGIN' => array(
      'email' => 'required_without:phone|string|regex:/^[a-z][a-z0-9_\.]{2,}@[a-z0-9]{2,}(\.[a-z]{2,}){1,2}$/',
      'phone' => 'required_without:email|regex:/^[0-9]{2,3}[0-9]{9}$/|max:12',
      'password'  => 'required|min:6|max:16|string'
    ),
    'RULE_REQUEST_RESET_PASSWORD' => array(
      'email' => 'required|regex:/^[a-z][a-z0-9_\.]{2,}@[a-z0-9]{2,}(\.[a-z]{2,}){1,2}$/|string',
    ),
    'RULE_ACCEPT_RESET_PASSWORD' => array(
      'password'  => 'required|min:6|max:16|string',
      'confirmPassword' => 'required|min:6|max:16|string|same:password'
    ),
    'RULE_CHANGE_PASSWORD' => array(
      'currentPassword'  => 'required|min:6|max:16|string',
      'newPassword'  => 'required|min:6|max:16|string',
      'confirmNewPassword'  => 'required|min:6|max:16|string|same:newPassword',
    ),
    'RULE_EDIT_PROFILE' => array(
      'name' => 'required|min:5',
      'gender' => 'required|boolean',
      'dateOfBirth' => 'required|date_format:d/m/Y',
      'about' => 'string|max:100'
    )
  );
}
