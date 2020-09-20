<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\AdminUser;
use App\Models\User;

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
     
     Route::post('forgot-password', 'Auth\AuthController@forgotPassword')->name('forgot-password');
     Route::post('reset-password', 'Auth\AuthController@resetPassword')->name('reset-password');  
     //admin
     Route::get('/get-admin-users', 'Auth\AuthController@getAdminUsers')->middleware(['auth:api', 'scopes:manage,transection,vault']);
     Route::post('/admin-login', 'Auth\AuthController@adminLogin')->name('admin-login');
     Route::post('admin-register', 'Auth\AuthController@adminRegister')->name('admin-register');

     Route::group([
        'middleware' => 'auth:api'
      ], function() {
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
        Route::post('/mobile-verify', 'User\UserController@verifyPhoneNumber')->name('mobile-verify');
        Route::post('/mail-mobile-code', 'User\UserController@mailMobileCode')->name('mail-mobile-code');
        Route::post('/turn-on-sale-user', 'User\UserController@turnOnSale')->name('turn-on-sale-user');
        Route::post('/get-delivered-bullians', 'User\UserController@getDeliveredFromUser')->name('get-delivered-bullians');
        Route::get('/user-vault-summary', 'User\UserController@userVaultSummary')->name('user-vault-summary');
        Route::get('/user-vault-history', 'User\UserController@userVaultHistory')->name('user-vault-history');

        //admin
        Route::post('/create-vault', 'User\UserController@createVault')->name('create-vault');
        Route::get('/get-vault', 'User\UserController@getVault')->name('get-vault');
        Route::delete('delete-vault/{id}', 'User\UserController@deleteVault')->name('delete-vault');
        Route::post('/update-vault', 'User\UserController@updateVault')->name('update-vault');
        Route::post('/vault-approval', 'User\UserController@vaultApproval')->name('vault-approval');
        Route::get('/get-vault-item', 'User\UserController@getVaultItem')->name('get-vault-item');
        Route::get('/get-detail-vault-item', 'User\UserController@getDetailVaultItem')->name('get-detail-vault-item');
        Route::get('/get-detail-vault-summary', 'User\UserController@getSummaryVaultItem')->name('get-detail-vault-summary');
        Route::post('/get-vault-search', 'User\UserController@getVaultSearch')->name('get-vault-search');
        Route::get('/print-pdf', 'User\UserController@printPDF')->name('print-pdf');
        Route::post('/modify-sale-state-admin', 'User\UserController@modifySaleAdmin')->name('modify-sale-state-admin');
        Route::post('/modify-delivery-state-admin', 'User\UserController@modifyDeliveryAdmin')->name('modify-delivery-state-admin');
    });
});
