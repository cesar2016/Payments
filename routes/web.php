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
// EXAMPLE
//Route::get('/doctors/seed', 'App\Http\Controllers\Admin\DoctorController@storeSeed');

Route::get('/', function () {
    return view('welcome');
});

Route::post('payments/pay', 'App\Http\Controllers\PaymentController@pay' )->name('pay');
Route::get('payments/approval', 'App\Http\Controllers\PaymentController@approval' )->name('approval');
Route::get('payments/cancelled', 'App\Http\Controllers\PaymentController@cancelled' )->name('cancelled');

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

