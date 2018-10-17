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


Route::get('sendsms', 'SmsApiController@sendSms');

Route::get('sendemail', 'MessageEmailController@sendEmail');

Route::get('customers/phoneemail', 'CustomerController@getByPhoneEmail');
Route::get('customers/addphoneemail', 'CustomerController@addPhoneEmail');

Route::get('orders/changestatus', 'OrderController@changeStatus');

Route::get('messages/send', 'MessageController@sendMessage');

Route::get('emails/send', 'SputnikEmailController@sendMessage');

Route::get('newpost/city', 'NewPostCityController@index');
Route::get('newpost/warehouses', 'NewPostCityController@warehouses');
Route::get('newpost/validate', 'NewPostCityController@isAddressValide');
Route::get('newpost/getttn', 'NewPostApiController@getTtn');

Route::resource('products', 'ProductController');
Route::resource('messages', 'MessageController');
Route::resource('autoreply', 'AutoReplyController');
Route::resource('groups', 'GroupsController');
Route::resource('orders', 'OrderController');
Route::resource('orderstatus', 'OrderStatusController');
Route::resource('customers', 'CustomerController');
Route::resource('messagetpl', 'MessageTemplateController');
Route::resource('settings', 'SettingsController');
Route::resource('autoreceive', 'AutoReceiveController');
Route::resource('dictionary', 'DictionaryController');

Route::prefix('statistics')->group(function () {
  Route::get('recalc/customers', 'CustomerController@recalcStatistics');
});


Route::prefix('sync')->group(function () {
  Route::get('products', 'SyncController@products');
  Route::get('orders', 'SyncController@orders');
  Route::get('messages', 'SyncController@messages');
  Route::get('test', 'SyncController@testapi');
  Route::get('smsstatus', 'SyncController@smsStatus');
  Route::get('newpost', 'SyncController@newPost');
});

Route::prefix('mass')->group(function () {
  Route::get('delivered', 'MassActionController@statusDelivered');
});

Route::get('import', 'ImportController@import');

Route::get('test', 'TestController@index');
