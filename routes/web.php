<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('chat-app')->group(function() {
    Auth::routes();
    Route::middleware(['auth'])->group(function () {
        Route::get('/home', 'HomeController@index')->name('home');
        Route::get('/conversation/{userId}', 'MessageController@conversation')->name('message.conversation');
        Route::post('send-message', 'MessageController@sendMessage')->name('message.sendMessage');
        Route::post('send-group-message', 'MessageController@sendGroupMessage')->name('message.sendGroupMessage');
        Route::resource('message-group', 'MessageGroupController');
    });
});
