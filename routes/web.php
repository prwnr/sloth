<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes();

Route::group(['middleware' => ['guest']], function () {
    //Guest routes
});

Route::group(['middleware' => ['auth']], function () {
    Route::post('changePassword', 'Auth\ChangePasswordController@changePassword')->name('password.change');
    Route::get('changePassword', 'Auth\ChangePasswordController@index')->name('password.change.index');
});

Route::group(['middleware' => ['auth', 'firstLogin']], function () {
    Route::get('/{any}', 'HomeController@index')->where('any', '.*');
});
