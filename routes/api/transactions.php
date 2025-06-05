<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\TransactionController;

Route::group(['prefix' => 'transactions'], function () {
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/recharge', [TransactionController::class, 'recharge']);
        Route::post('/send-money', [TransactionController::class, 'sendMoney']);
        Route::get('/received-transactions', [TransactionController::class, 'receivedTransactions']);
        Route::get('/sent-transactions', [TransactionController::class, 'sentTransactions']);
        Route::get('/all-transactions', [TransactionController::class, 'allTransactions']);
    });
});
