<?php

use Illuminate\Http\Request;

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

/**
 * Auth Route's
 */
Route::post('/login', 'Api\AuthController@login');
Route::post('/signup', 'Api\AuthController@signup');
Route::get('/get/profile', 'Api\AuthController@getProfile');
Route::post('/update/profile', 'Api\AuthController@updateProfile');
Route::post('/change/password', 'Api\AuthController@changePassword');
Route::post('/forgot/password', 'Api\AuthController@forgotPassword');

/**
 * Contact Route's
 */
Route::get('/get/contacts', 'Api\ContactController@index');
Route::post('/create/contact', 'Api\ContactController@create');
Route::get('/get/contact', 'Api\ContactController@show');
Route::post('/update/contact', 'Api\ContactController@update');
Route::post('/delete/contact', 'Api\ContactController@destroy');
