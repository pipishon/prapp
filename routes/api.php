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

Route::middleware('auth')->group(function () {


Route::get('sendsms', 'SmsApiController@sendSms');

Route::get('sendemail', 'MessageEmailController@sendEmail');

Route::get('customers/phoneemail', 'CustomerController@getByPhoneEmail');
Route::get('customers/addphoneemail', 'CustomerController@addPhoneEmail');

Route::get('orders/changestatus', 'OrderController@changeStatus');
Route::get('orders/updatefromprom/{prom_id}', 'OrderController@updateFromProm');
Route::get('orders/importfromapi', 'OrderController@ImportFromApi');
Route::get('orders/refresh/{id}', 'OrderController@refresh');
Route::get('orders/getgroups', 'OrderController@getGroups');
Route::get('orders/getbygroup', 'OrderController@getProductByGroup');
Route::get('orders/sendfeedback/{prom_id}', 'OrderController@sendFeedBack');

Route::get('messages/send', 'MessageController@sendMessage');

Route::get('emails/send', 'SputnikEmailController@sendMessage');

Route::get('newpost/city', 'NewPostCityController@index');
Route::get('newpost/warehouses', 'NewPostCityController@warehouses');
Route::get('newpost/validate', 'NewPostCityController@isAddressValide');
Route::get('newpost/getttn', 'NewPostApiController@getTtn');

Route::resource('products', 'ProductController');
Route::resource('discounts', 'DiscountController');

Route::resource('orderproducts', 'OrderProductController');
Route::post('orderproducts/massdiscount', 'OrderProductController@massDiscount');

Route::get('purchase', 'PurchaseController@index');

Route::prefix('purchase')->group(function () {
  Route::post('/save', 'PurchaseController@store');
  Route::get('/getsaveddates', 'PurchaseController@getSavedDates');
});

Route::prefix('product')->group(function () {
  Route::get('dashboardstats', 'ProductController@dashboardStats');
  Route::get('calcabc', 'ProductController@recalcAbc');
  Route::get('calcabcqty', 'ProductController@calcABCQty');
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
  Route::get('calctoday', 'OrderDayStatisticController@calcToday');
  Route::get('calcmonth', 'OrderDayStatisticController@calcMonth');
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
  Route::get('pdf', 'MassActionController@createPdf');
  Route::get('discount', 'MassActionController@setDiscount');
  Route::get('removediscount', 'MassActionController@removeDiscount');
});

Route::get('import', 'ImportController@import');
Route::get('importproducts', 'ImportController@importproducts');
Route::get('importorderproducts', 'ImportController@processOrderProducts');

Route::get('test', 'TestController@index');
Route::get('test/test1', 'TestController@test1');
Route::get('test/test2', 'TestController@test2');
Route::get('test/instagram', 'TestController@instagram');
Route::get('test/{action}', 'TestController@actionProccess');


Route::resource('pack', 'PackController');

Route::get('pdf/invoice/{id}', 'PdfController@invoice');
Route::get('pdf/view/{id}', 'PdfController@view');

Route::get('rfc', 'RfcController@index');
Route::prefix('rfc')->group(function () {
  Route::get('store', 'RfcController@store');
  Route::get('update', 'RfcController@updateAutoStatus');
  Route::get('saved', 'RfcController@getSaved');
  Route::get('getdates', 'RfcController@getAvailableSavedDates');
  Route::get('gettoday', 'RfcController@getToday');
  Route::get('statistic/{name}', 'RfcController@statistic');
});
});

Route::get('votes', 'VoteController@index');
Route::get('votesemail', 'VoteController@getEmails');

Route::get('crons', 'CronController@index');
Route::post('crons', 'CronController@store');
