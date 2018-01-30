<?php

// use Illuminate\Http\Request;

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

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });


Route::group(['prefix' => 'api'], function () {
  /**
   * Authentication routes
   */
  Route::group(['prefix' => 'auth'], function() {
      // Route::post('login',         'API\UsersController@login');
      // Route::post('logout',        'API\UsersController@logout');
      Route::get('register',      'API\UsersController@register');
      // Route::get('refresh-token',  'API\UsersController@refreshToken');
      // Route::get('getUser',        'API\UsersController@getUser');
  });
});