<?php

namespace App\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class FriendRequestList extends BaseModel
{
  use SoftDeletes;
  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $dates = ['deleted_at'];
  protected $fillable = [
    'user_id', 'friend_id', 'status'
  ];

  public function users()
  {
    return $this->belongsto('App\User');
  }
}
