<?php

namespace App\Http\Controllers;

use App\Models\FriendList;
use App\User;
use App\Models\FriendRequestList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Exception;

class FriendController extends BaseController
{
  public function __construct(Request $request)
  {
    $this->request = $request;
    $this->error = Lang::get('errorCodes');
    parent::__construct($request);
  }

  /**
   * @SWG\POST(
   *      path="/friend/{friend_id}/request-add-friend",
   *      operationId="requestAddFriend",
   *      tags={"Friends"},
   *      summary="Request Add Friend",
   *      security={{"jwt":{}}},
   *      description="Request Add Friend",
   *       @SWG\Parameter( name="friend_id", description="", required=true, type="string", in="path" ),

   *      @SWG\Response(response=200, description="Request Add Friend successfully"),
   *      @SWG\Response(response="400", description="Bad Request"),
   *      @SWG\Response(response="422", description="Unprocessable Entity"),
   *      @SWG\Response(response="500", description="Internal Server Error"),
   * )
   *
   */

  public function requestAddFriend(Request $request, $friend_id)
  {
    try {
      $checkFriend = User::where('id', $friend_id)->first();
      if (!$checkFriend) {
        return $this->respondWithCustomError('friend_not_found', 401);
      }
      $friendRequest = FriendRequestList::updateOrCreate([
        'user_id' => $request->userData->id,
        'friend_id' => $friend_id,
      ], [
        'status' => 1,
      ]);

      $result = [
        'friendRequest' => $friendRequest
      ];
      return $this->respondSuccess($result);
    } catch (Exception $e) {
      return $this->InternalServerError($e->getMessage());
    }
  }


  /**
   * @SWG\POST(
   *      path="/friend/list-add-friend/{friend_request_id}",
   *      operationId="acceptAddFriend",
   *      tags={"Friends"},
   *      summary="Accept Add Friend",
   *      security={{"jwt":{}}},
   *      description="Accept Add Friend",
   *      @SWG\Parameter( name="friend_request_id", description="", required=true, type="string", in="path" ),

   *      @SWG\Response(response=200, description="Accept Add Friend successfully"),
   *      @SWG\Response(response="400", description="Bad Request"),
   *      @SWG\Response(response="422", description="Unprocessable Entity"),
   *      @SWG\Response(response="500", description="Internal Server Error"),
   * )
   *
   */

  public function acceptAddFriend(Request $request, $friend_request_id)
  {
    try {
      $findFriendRequest = FriendRequestList::where('friend_id', $request->userData->id)->where('id', $friend_request_id)->first();
      if (!$findFriendRequest) {
        return $this->respondWithCustomError('friend_not_found', 401);
      }
      $friendAccept = new FriendList;
      $friendAccept->user_id = $findFriendRequest->friend_id;
      $friendAccept->friend_id = $findFriendRequest->user_id;
      $friendAccept->save();

      $friendRequest = new FriendList;
      $friendRequest->user_id = $findFriendRequest->user_id;
      $friendRequest->friend_id = $findFriendRequest->friend_id;
      $friendRequest->save();

      $findFriendRequest->delete();

      $result = [
        'friend' => $friendAccept,
      ];
      return $this->respondSuccess($result);
    } catch (Exception $e) {
      return $this->InternalServerError($e->getMessage());
    }
  }


  /**
   * @SWG\GET(
   *      path="/friend/list-add-friend",
   *      operationId="GetListAddFriend",
   *      tags={"Friends"},
   *      summary="Get List Request Add Friend",
   *      security={{"jwt":{}}},
   *      description="Get List Request Add Friend",
   *     
   *      @SWG\Response(response=200, description="Get List Add Friend Successfully"),
   *      @SWG\Response(response="400", description="Bad Request"),
   *      @SWG\Response(response="422", description="Unprocessable Entity"),
   *      @SWG\Response(response="500", description="Internal Server Error"),
   * )
   *
   */

  public function getFriendRequestList(Request $request)
  {
    try {
      $friendRequestList = FriendRequestList::select('friend_request_lists.id', 'users.name', 'users.email', 'users.phone', 'users.gender', 'users.about', 'friend_request_lists.updated_at')
        ->join('users', 'friend_request_lists.user_id', '=', 'users.id')
        ->where('friend_request_lists.friend_id', '=', $request->userData->id)
        ->orderBy('friend_request_lists.updated_at', 'desc')
        ->get();
      if (!$friendRequestList) {
        return $this->respondWithCustomError('friend_list_not_found', 401);
      }

      return $this->respondSuccess($friendRequestList);
    } catch (Exception $e) {
      return $this->InternalServerError($e->getMessage());
    }
  }

  /**
   * @SWG\GET(
   *      path="/friend/list-friend",
   *      operationId="GetListFriend",
   *      tags={"Friends"},
   *      summary="Get List Friend",
   *      security={{"jwt":{}}},
   *      description="Get List Friend",
   *     
   *      @SWG\Response(response=200, description="Get List Friend Successfully"),
   *      @SWG\Response(response="400", description="Bad Request"),
   *      @SWG\Response(response="422", description="Unprocessable Entity"),
   *      @SWG\Response(response="500", description="Internal Server Error"),
   * )
   *
   */

  public function getFriendList(Request $request)
  {
    try {
      $friendList = FriendList::select('users.name', 'users.email', 'users.phone', 'users.gender', 'users.about', 'friend_lists.updated_at','friend_lists.id')
        ->join('users', 'friend_lists.friend_id', '=', 'users.id')
        ->where('friend_lists.user_id', '=', $request->userData->id)
        ->orderBy('friend_lists.updated_at', 'desc')
        ->get();
      if (!$friendList) {
        return $this->respondWithCustomError('friend_list_not_found', 401);
      }

      return $this->respondSuccess($friendList);
    } catch (Exception $e) {
      return $this->InternalServerError($e->getMessage());
    }
  }

  /**
   * @SWG\GET(
   *      path="/friend/{friend_list_id}/friend-profile",
   *      operationId="GetFriendProfile",
   *      tags={"Friends"},
   *      summary="Get Friend Profile",
   *      security={{"jwt":{}}},
   *      description="Get Friend Profile",
   *      @SWG\Parameter( name="friend_list_id", description="", required=true, type="string", in="path" ),

   *      @SWG\Response(response=200, description="Get Friend Profile Successfully"),
   *      @SWG\Response(response="400", description="Bad Request"),
   *      @SWG\Response(response="422", description="Unprocessable Entity"),
   *      @SWG\Response(response="500", description="Internal Server Error"),
   * )
   *
   */

  public function getFriendProfile(Request $request, $friend_list_id)
  {
    try {
      $friendList = FriendList::select('users.name', 'users.email', 'users.phone', 'users.gender', 'users.about', 'friend_lists.updated_at')
        ->join('users', 'friend_lists.friend_id', '=', "users.id")
        ->where('friend_lists.user_id', '=', $request->userData->id)
        ->where('friend_lists.id', '=', $friend_list_id)
        ->first();
      if (!$friendList) {
        return $this->respondWithCustomError('friend_not_found', 401);
      }

      return $this->respondSuccess($friendList);
    } catch (Exception $e) {
      return $this->InternalServerError($e->getMessage());
    }
  }
}
