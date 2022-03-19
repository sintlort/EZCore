<?php

use App\Http\Controllers\API\AccountManagement;
use App\Http\Controllers\API\ScheduleManagement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::controller(AccountManagement::class)->group(function (){
    Route::post('/login','login');
    Route::post('/register','register');

    Route::middleware(['auth:sanctum'])->group(function (){
        Route::post('/logout','logout');
    });
});

Route::middleware(['auth:sanctum'])->group(function (){
    Route::controller(ScheduleManagement::class)->group(function (){
        Route::get('pelabuhan/all', 'indexPelabuhan');
        Route::post('schedule/search', 'searchSchedule');
    });
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
