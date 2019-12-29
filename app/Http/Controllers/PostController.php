<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\User;
use App\Models\FriendList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Exception;

class PostController extends BaseController
{
  public function __construct(Request $request)
  {
    $this->request = $request;
    $this->error = Lang::get('errorCodes');
    parent::__construct($request);
  }

  /**
   * @SWG\POST(
   *      path="/status",
   *      operationId="addStatus",
   *      tags={"Posts"},
   *      summary="Add status",
   *      security={{"jwt":{}}},
   *      description="Add status",
   *     
   *       @SWG\Parameter(
   *          name="body", description="Add status", required=true, in="body",
   *          @SWG\Schema(
   *               @SWG\Property(property="title", type="string", default="Hello Everyone"),
   *               @SWG\property(property="content", type="string", default="I am admin ahihihi....")
   *          ),
   *      ),
   *      @SWG\Response(response=200, description="Edit profile successfully"),
   *      @SWG\Response(response="400", description="Bad Request"),
   *      @SWG\Response(response="422", description="Unprocessable Entity"),
   *      @SWG\Response(response="500", description="Internal Server Error"),
   * )
   *
   */

  public function addStatus(Request $request)
  {
    try {
      $input = $request->all();
      $validatorError = Post::validate($input, 'RULE_ADD_STATUS');

      if (!empty($validatorError)) {
        return $this->respondWithError($validatorError);
      }

      $post = new Post();
      $post->user_id = $request->userData->id;
      $post->title = $request->title;
      $post->content = $request->content;
      $post->save();
      $user = $post->users()->first();
      $post->setAttribute("name", $user->name);

      $result = [
        'status' => $post,
      ];
      return $this->respondSuccess($result);
    } catch (Exception $e) {
      return $this->InternalServerError($e->getMessage());
    }
  }

  /**
   * @SWG\GET(
   *      path="/status",
   *      operationId="GetListStatus",
   *      tags={"Posts"},
   *      summary="Get List Status",
   *      security={{"jwt":{}}},
   *      description="Get List Status",
   *     
   *      @SWG\Response(response=200, description="Get List Status Successfully"),
   *      @SWG\Response(response="400", description="Bad Request"),
   *      @SWG\Response(response="422", description="Unprocessable Entity"),
   *      @SWG\Response(response="500", description="Internal Server Error"),
   * )
   *
   */

  public function getListStatus(Request $request)
  {
    try {
      $post = Post::select('users.name', 'posts.*')
        ->join('users', 'posts.user_id', '=', 'users.id')
        ->Where('posts.user_id', $request->userData->id)
        ->orderBy('posts.updated_at', 'desc')
        ->get();
      if (!$post) {
        return $this->respondWithCustomError('status_not_found', 401);
      }

      return $this->respondSuccess($post);
    } catch (Exception $e) {
      return $this->InternalServerError($e->getMessage());
    }
  }

  /**
   * @SWG\GET(
   *      path="/status/{id}",
   *      operationId="GetStatus",
   *      tags={"Posts"},
   *      summary="Get Status By Id",
   *      security={{"jwt":{}}},
   *      description="Get Status By Id",
   *      @SWG\Parameter( name="id", description="", required=true, type="string", in="path" ),
   *      @SWG\Response(response=200, description="Get status Successfully"),
   *      @SWG\Response(response="400", description="Bad Request"),
   *      @SWG\Response(response="422", description="Unprocessable Entity"),
   *      @SWG\Response(response="500", description="Internal Server Error"),
   * )
   *
   */

  public function getStatus(Request $request, $id)
  {
    try {
      $post = Post::where('user_id', $request->userData->id)->where('id', $id)->first();
      if (!$post) {
        return $this->respondWithCustomError('status_not_found', 401);
      }

      return $this->respondSuccess($post);
    } catch (Exception $e) {
      return $this->InternalServerError($e->getMessage());
    }
  }

  /**
   * @SWG\PUT(
   *      path="/status/{id}",
   *      operationId="EditStatus",
   *      tags={"Posts"},
   *      summary="Edit Status By Id",
   *      security={{"jwt":{}}},
   *      description="Edit Status By Id",
   *      @SWG\Parameter( name="id", description="", required=true, type="string", in="path" ),
   *      @SWG\Parameter(
   *          name="body", description="edit status", required=true, in="body",
   *          @SWG\Schema(
   *              @SWG\Property(property="title", type="string", default="Ahihi..."),
   *              @SWG\Property(property="content", type="string", default="Still ahihihi...."),
   *          ),
   *      ),
   *      @SWG\Response(response=200, description="Edit status Successfully"),
   *      @SWG\Response(response="400", description="Bad Request"),
   *      @SWG\Response(response="422", description="Unprocessable Entity"),
   *      @SWG\Response(response="500", description="Internal Server Error"),
   * )
   *
   */

  public function editStatus(Request $request, $id)
  {
    try {
      $post = Post::where('user_id', $request->userData->id)->where('id', $id)->first();

      if (!$post) {
        return $this->respondWithCustomError('status_not_found', 401);
      }
      $post->title = $request->title;
      $post->content = $request->content;
      $post->save();

      return $this->respondSuccess($post);
    } catch (Exception $e) {
      return $this->InternalServerError($e->getMessage());
    }
  }

  /**
   * @SWG\DELETE(
   *      path="/status/{id}",
   *      operationId="DeleteStatus",
   *      tags={"Posts"},
   *      summary="Delete Status By Id",
   *      security={{"jwt":{}}},
   *      description="Delete Status By Id",
   *      @SWG\Parameter( name="id", description="", required=true, type="string", in="path" ),
   * 
   *      @SWG\Response(response=200, description="Edit status Successfully"),
   *      @SWG\Response(response="400", description="Bad Request"),
   *      @SWG\Response(response="422", description="Unprocessable Entity"),
   *      @SWG\Response(response="500", description="Internal Server Error"),
   * )
   *
   */

  public function deleteStatus(Request $request, $id)
  {
    try {
      $post = Post::where('user_id', $request->userData->id)->where('id', $id)->first();

      if (!$post) {
        return $this->respondWithCustomError('status_not_found', 401);
      }

      $post->delete();

      return $this->respondSuccess($post);
    } catch (Exception $e) {
      return $this->InternalServerError($e->getMessage());
    }
  }

  /**
   * @SWG\GET(
   *      path="/friend/{friend_list_id}/status",
   *      operationId="GetFriendStatus",
   *      tags={"Posts"},
   *      summary="Get Friend Status",
   *      security={{"jwt":{}}},
   *      description="Get Friend Status",
   *      @SWG\Parameter( name="friend_list_id", description="", required=true, type="string", in="path" ),
   *     
   *      @SWG\Response(response=200, description="Get Friend Status Successfully"),
   *      @SWG\Response(response="400", description="Bad Request"),
   *      @SWG\Response(response="422", description="Unprocessable Entity"),
   *      @SWG\Response(response="500", description="Internal Server Error"),
   * )
   *
   */

  public function getFriendStatus(Request $request, $friend_list_id)
  {
    try {
      $post = Post::select('users.name', 'posts.*','friend_lists.id as friend_lists_id')
        ->join('users', 'posts.user_id', '=', 'users.id')
        ->join('friend_lists', 'posts.user_id', '=', 'friend_lists.friend_id')
        ->where('friend_lists.user_id', '=', $request->userData->id)
        ->where('friend_lists.id', '=', $friend_list_id)
        ->orderBy('posts.updated_at', 'desc')
        ->get();

      if (!$post) {
        return $this->respondWithCustomError('status_not_found', 401);
      }
      return $this->respondSuccess($post);
    } catch (Exception $e) {
      return $this->InternalServerError($e->getMessage());
    }
  }

  /**
   * @SWG\GET(
   *      path="/friend/{friend_list_id}/status/{status_id}",
   *      operationId="GetFriendStatusById",
   *      tags={"Posts"},
   *      summary="Get Friend Status By Id",
   *      security={{"jwt":{}}},
   *      description="Get Friend Status By Id",
   *      @SWG\Parameter( name="friend_list_id", description="", required=true, type="string", in="path" ),
   *      @SWG\Parameter( name="status_id", description="", required=true, type="string", in="path" ),
   *     
   *      @SWG\Response(response=200, description="Get Friend Status By Id Successfully"),
   *      @SWG\Response(response="400", description="Bad Request"),
   *      @SWG\Response(response="422", description="Unprocessable Entity"),
   *      @SWG\Response(response="500", description="Internal Server Error"),
   * )
   *
   */

  public function getFriendStatusById(Request $request, $friend_list_id, $status_id)
  {
    try {
      $post = Post::select('users.name', 'posts.*')
        ->join('users', 'posts.user_id', '=', 'users.id')
        ->join('friend_lists', 'posts.user_id', '=', 'friend_lists.friend_id')
        ->where('friend_lists.user_id', '=', $request->userData->id)
        ->where('friend_lists.id', '=', $friend_list_id)
        ->where('posts.id', '=', $status_id)
        ->orderBy('posts.updated_at', 'desc')
        ->get();

      if (!$post) {
        return $this->respondWithCustomError('status_not_found', 401);
      }

      return $this->respondSuccess($post);
    } catch (Exception $e) {
      return $this->InternalServerError($e->getMessage());
    }
  }

  /**
   * @SWG\GET(
   *      path="/new-feed",
   *      operationId="GetNewFeed",
   *      tags={"Posts"},
   *      summary="Get New Feed",
   *      security={{"jwt":{}}},
   *      description="Get New Feed",
   *     
   *      @SWG\Response(response=200, description="Get New Feed Successfully"),
   *      @SWG\Response(response="400", description="Bad Request"),
   *      @SWG\Response(response="422", description="Unprocessable Entity"),
   *      @SWG\Response(response="500", description="Internal Server Error"),
   * )
   *
   */

  public function getNewFeed(Request $request)
  {
    try {
      $yourListPost = Post::select('users.name', 'posts.*','friend_lists.id as friend_lists_id')
        ->join('users', 'posts.user_id', '=', 'users.id')
        ->join('friend_lists', 'posts.user_id', '=', 'friend_lists.friend_id')
        ->where('friend_lists.user_id', $request->userData->id)
        ->orWhere('posts.user_id', $request->userData->id)
        ->orderBy('posts.updated_at', 'desc')
        ->distinct()
        ->get();
      $mylistPost = Post::select('users.name', 'posts.*')
        ->join('users', 'posts.user_id', '=', 'users.id')
        ->Where('posts.user_id', $request->userData->id)
        ->orderBy('posts.updated_at', 'desc')
        ->distinct()
        ->get();

        $result =  $yourListPost->merge($mylistPost);

      return $this->respondSuccess($result);
    } catch (Exception $e) {
      return $this->InternalServerError($e->getMessage());
    }
  }
}
