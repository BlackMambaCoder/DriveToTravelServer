<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

//Route::get('/', function () {
//    return view('welcome');
//});

Route::get('/', 'UserController@index');
Route::any('user/register', 'UserController@store');
Route::any('user/login', 'UserController@authenticate');
Route::post('user/add_friend', 'UserController@addFriend');

Route::get('tour/{username}/rank/{rank_value}', 'TourController@rankDriversTour');
Route::any('tour/create', 'TourController@store');
Route::any('tour/drivers', 'TourController@getDriversTours');
Route::any('tour/all', 'TourController@getAllTours');
Route::any('tour/update_rank', 'TourController@rankDriversTour');
Route::any('tour/search_by_loc', 'TourController@searchByLocation');

Route::post('location/{userId}', 'LocationController@store');

