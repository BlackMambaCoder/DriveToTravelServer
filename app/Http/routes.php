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

Route::get('/', function () {
    return view('welcome');
});

Route::post('user', 'UserController@store');
Route::post('user/add_friend', 'UserController@addFriend');

Route::get('tour/{username}/rank/{rank_value}', 'TourController@rankDriversTour');
Route::post('tour/store', 'TourController@store');

Route::post('location/{userId}', 'LocationController@store');

