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
Route::get('orders/updatefromprom/{prom_id}', 'OrderController@updateFromProm');
Route::get('orders/importfromapi', 'OrderController@ImportFromApi');

Route::get('messages/send', 'MessageController@sendMessage');

Route::get('emails/send', 'SputnikEmailController@sendMessage');

Route::get('newpost/city', 'NewPostCityController@index');
Route::get('newpost/warehouses', 'NewPostCityController@warehouses');
Route::get('newpost/validate', 'NewPostCityController@isAddressValide');
Route::get('newpost/getttn', 'NewPostApiController@getTtn');

Route::resource('products', 'ProductController');

Route::prefix('product')->group(function () {
  Route::get('suplier', 'ProductController@getSuplierProducts');
  Route::get('morders', 'ProductController@syncProductMonthOrders');
  Route::post('import', 'ProductController@importProcess');
  Route::get('importfromapi', 'ProductController@importFromApiProcess');
  Route::get('ordermonth/{id}', 'ProductController@getOrderMonth');
  Route::get('addlabel', 'ProductController@addLabel');
  Route::get('addsuplierlink', 'ProductController@addSuplierLink');
  Route::get('updatesuplierlink', 'ProductController@updateSuplierLink');
  Route::get('removelabel', 'ProductController@removeLabel');
  Route::get('addsuplier', 'ProductController@addSuplier');
  Route::get('removesuplier', 'ProductController@removeSuplier');
  Route::get('massupdate', 'ProductController@massUpdate');
});

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

Route::resource('nptrack', 'NewPostTtnTrackController');
Route::get('nptrackcheck', 'NewPostTtnTrackController@checkStatus');
Route::get('addttntotrack', 'NewPostTtnTrackController@addTtn');

Route::resource('orderdaystatistic', 'OrderDayStatisticController');

Route::resource('suplier', 'SuplierController');

Route::resource('labelp', 'LabelpController');

Route::prefix('statistics')->group(function () {
  Route::get('recalc/customers', 'CustomerController@recalcStatistics');
  Route::get('recalc/orders', 'OrderDayStatisticController@recalcStatistics');
});



Route::prefix('sync')->group(function () {
  Route::get('products', 'SyncController@products');
  Route::get('orders', 'SyncController@orders');
  Route::get('orderproducts', 'SyncController@OrderProducts');
  Route::get('messages', 'SyncController@messages');
  Route::get('test', 'SyncController@testapi');
  Route::get('smsstatus', 'SyncController@smsStatus');
  Route::get('newpost', 'SyncController@newPost');
});

Route::prefix('mass')->group(function () {
  Route::get('delivered', 'MassActionController@statusDelivered');
  Route::get('sendttn', 'MassActionController@sendTtn');
  Route::get('createttn', 'MassActionController@createTtn');
});

Route::get('import', 'ImportController@import');
Route::get('importproducts', 'ImportController@importproducts');
Route::get('importorderproducts', 'ImportController@processOrderProducts');

Route::get('test', 'TestController@index');
