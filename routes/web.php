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
use App\Http\Middleware\ApiVerifyMiddleware;

Route::get('/', function () {
    return view('welcome');
});

//Route::group(['middleware' => ['api.signature']], function () {
//
//    Route::get('demo', 'Demo\FirstController@index');
//    Route::get('demo2', 'Demo\FirstController@index2');
//    Route::get('demo3', 'Demo\FirstController@index3');
//});


Route::group(['as' => '测试.第一个.'], function () {
    Route::get('api/demo', 'Demo\FirstController@index')->name('示例接口');

});
