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
    //  Route::post('login', 'Auth\AuthController@login')->name('login');
     Route::post('register', 'Auth\AuthController@register')->name('register');  
     Route::post('admin-register', 'Auth\AuthController@adminRegister')->name('admin-register');
     Route::post('forgot-password', 'Auth\AuthController@forgotPassword')->name('forgot-password');
     Route::post('reset-password', 'Auth\AuthController@resetPassword')->name('reset-password');  

     Route::group([
        'middleware' => 'auth:api'
      ], function() {
          Route::post('phone-verification', 'Auth\AuthController@verifyPhoneNumber')->name('phone-verification');
          Route::post('schedule-call-time', 'Auth\AuthController@scheduleCallTimeForContact')->name('schedule-call-time');
          Route::post('image-upload', 'Auth\AuthController@imageUpload')->name('image-upload');
          Route::post('logout', 'Auth\AuthController@logout')->name('logout');
          Route::get('user', 'Auth\AuthController@user');
   });
});

Route::group(['prefix' => 'users'], function () {
    Route::group(['middleware' => 'auth:api'], function () {
        Route::get('/get-users', 'User\UserController@getUsers')->name('get-users');
        Route::get('/filter-users/{status}', 'User\UserController@filterUsers')->name('filter-users-by-status');
        Route::get('/search-users', 'User\UserController@searchUsers')->name('search-users');
        Route::get('/get-details/{name}', 'User\UserController@getUserBySlug')->name('get-details');
        Route::post('/verify-users', 'User\UserController@verifyUsers')->name('verify-users');
        Route::post('/delete-users', 'User\UserController@deleteUsers')->name('delete-users');
    });
});