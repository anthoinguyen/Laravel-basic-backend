<?php

namespace App\Models;

use App\Models\BaseModel;

class Post extends BaseModel
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'title', 'post'
    ];

    public function users()
    {
    	return $this->belongsto('App\User',"user_id","id");
    }

    public function friend_lists()
  {
    return $this->belongsto('App\Models\FriendList','friend_id','id');
  }

    public static $rules = array(
        'RULE_ADD_STATUS' => array(
          'title' => 'required|string|max:100',
          'content' => 'required|string|max:255'
        )
      );

}
