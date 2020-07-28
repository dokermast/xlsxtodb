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

Route::get('/', 'UploadController@getForm')->name('form');

Route::post('/upload', 'UploadController@upload')->name('upload');

Route::get('/trunkate', 'UploadController@trunkate')->name('trunkate');




