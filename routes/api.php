<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


Route::group([
    'prefix' => 'auth'
], function () {
     Route::post('login', 'Auth\AuthController@login')->name('login');
     Route::post('register', 'Auth\AuthController@register')->name('register');
     Route::post('phone-verification', 'Auth\AuthController@verifyPhoneNumber')->name('phone-verification');
     Route::post('schedule-call-time', 'Auth\AuthController@scheduleCallTimeForContact')->name('schedule-call-time');
     Route::post('image-upload', 'Auth\AuthController@imageUpload')->name('image-upload');
     Route::group([
        'middleware' => 'auth:api'
      ], function() {
          Route::get('logout', 'Auth\AuthController@logout')->name('logout');
          Route::get('user', 'Auth\AuthController@user');
   });
});