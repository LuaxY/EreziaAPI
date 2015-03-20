<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::post('game/authentification.json', 'AccountController@auth');
Route::post('game/account.json', 'AccountController@info');
Route::post('game/shop.json', 'ShopController@shop');
