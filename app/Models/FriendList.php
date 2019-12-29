<?php

namespace App\Models;

use App\Models\BaseModel;

class FriendList extends BaseModel
{

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
    'user_id', 'friend_id'
  ];

  public function users()
  {
    return $this->belongsto('App\User','id','id');
  }

  public function posts()
  {
    return $this->hasMany('App\Models\Post', 'user_id','id');
  }
}
