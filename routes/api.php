<?php

use App\Http\Controllers\API\AccountManagement;
use App\Http\Controllers\API\ScheduleManagement;
use App\Http\Controllers\API\TransactionManagement;
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
        Route::get('/user','user');
        Route::post('/logout','logout');
    });
});

Route::middleware(['auth:sanctum'])->group(function (){
    Route::controller(TransactionManagement::class)->group(function (){
        Route::get('transaction/recently', 'getTransactionRecently');
        Route::get('transaction/history', 'getTransactionHistory');
        Route::POST('transaction/my/penumpang','getPenumpang');
        Route::POST('transaction/cancel','transactionCanceled');
    });

    Route::controller(ScheduleManagement::class)->group(function (){
        Route::get('pelabuhan/all', 'indexPelabuhan');
        Route::get('golongan/all', 'indexGolongan');
        Route::post('schedule/search', 'searchTestv1');
    });

    Route::controller(TransactionManagement::class)->group(function (){
        Route::get('metode/all', 'metodePembayaran');
        Route::POST('transaction/commited', 'transactionCommited');
        Route::POST('transaction/commited/penumpang', 'transactionCommitedForPenumpang');
        Route::POST('image/upload ', 'imageUpload');
        Route::post('check/ticket','checkTicket');
    });
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
