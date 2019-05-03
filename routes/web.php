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
Route::group(['domain' => 'feedback.'.env('APP_BASE_DOMAIN')], function (){
    Route::get('/', 'VoteController@getForm');
    Route::post('/', 'VoteController@processForm');
    Route::get('/success', 'VoteController@getSuccess')->name('vote.success');
    Route::get('/remoteclick', 'VoteController@remoteClick');
});

Route::middleware('auth')->get('/', function () {
    return view('welcome');
});

Route::get('invoice', 'PdfController@getInvoiceByLink');

Route::get('/import', 'ImportController@index');
Route::post('/importupload', 'ImportController@upload');

Route::get('/smstest', 'SmsApiController@test');

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
