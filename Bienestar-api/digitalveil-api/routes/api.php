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

Route::middleware('auth:api')->get('/general', function (Request $request) {
    return $request->user();
});

/* --------- USAGE SERVICE ---------- */
Route::get('/usage/{id}','UsageController@get');

Route::post('/usage/all', 'UsageController@getAll');
Route::post('csv','UsageController@storeCSV');
Route::post('/usage/map', 'UsageController@getMapData');
Route::post('/usage/create', 'UsageController@createOne');

/* --------- USER SERVICE ---------- */
Route::get('/user/{id}', 'UserController@get' );


Route::post('/user', 'UserController@create' );
Route::post('/user/login', 'UserController@login' );
Route::post('/user/password/reset', 'UserController@sendMail');
Route::post('/user/{id}/delete', 'UserController@delete' );

/* --------- APPLICATION SERVICE ---------- */
Route::get('/applications', 'ApplicationController@getAll' );


Route::post('/application', 'ApplicationController@create' );
Route::post('/application/update', 'ApplicationController@update' );

//NOT TESTED
Route::post('/application/{id}', 'ApplicationController@get' );
Route::post('/application/{id}/delete', 'ApplicationController@delete');

/* --------- RESTRICTICTION SERVICE ---------- */
Route::get('/restriction/{id}', 'RestrictionController@getAll' );
Route::get('/restriction/{id}/app/{appId}', 'RestrictionController@get' );

Route::post('/restriction', 'RestrictionController@create' );
Route::post('/restriction/{id}', 'RestrictionController@update' );
Route::post('/restriction/{id}/delete', 'RestrictionController@delete');

/* --------- STATISTICS SERVICE ---------- */

Route::get('/statistics/{id}/app/{appId}', 'UsageController@getAverage');