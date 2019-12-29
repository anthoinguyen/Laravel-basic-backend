<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['prefix' => 'auth'], function () {
  Route::post('register', 'AuthController@register');
  Route::post('login', 'AuthController@login');
  Route::post('request-reset-password', 'AuthController@requestResetPassword');
  Route::post('accept-reset-password', 'AuthController@acceptResetPassword');
});

Route::group(['middleware' => ['jwt.verify']], function () {
  Route::get('user', 'UserController@getProfileUser');
  Route::put('change-password', 'AuthController@changePassword');
  Route::put('edit-profile', 'UserController@editProfile');

  Route::prefix('status')->group(function () {
    Route::post('', 'PostController@addStatus');
    Route::get('', 'PostController@getListStatus');
    Route::get('{id}', 'PostController@getStatus');
    Route::put('{id}', 'PostController@editStatus');
    Route::delete('{id}', 'PostController@deleteStatus');
  });

  Route::get('new-feed', 'PostController@getNewFeed');

  Route::prefix('friend')->group(function () {
    Route::post('{friend_id}/request-add-friend', 'FriendController@requestAddFriend');
    Route::post('list-add-friend/{friend_request_id}', 'FriendController@acceptAddFriend');
    Route::get('list-add-friend', 'FriendController@getFriendRequestList');
    Route::get('list-friend', 'FriendController@getFriendList');
    Route::get('{friend_list_id}/friend-profile', 'FriendController@getFriendProfile');
    Route::get('{friend_list_id}/status', 'PostController@getFriendStatus');
    Route::get('{friend_list_id}/status/{status_id}', 'PostController@getFriendStatusById');
  });
});
