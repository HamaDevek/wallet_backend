<?php

use App\Http\Controllers\API\ApiController;
use App\Http\Controllers\API\AuthController;
use App\Http\Middleware\RateLimit\AuthRateLimitMiddleware;
use Illuminate\Support\Facades\Route;

Route::get("/health", [ApiController::class, "index"]);
Route::group(['prefix' => 'auth'], function () {
    Route::post('/login', [AuthController::class, 'login'])->middleware(AuthRateLimitMiddleware::class);
    Route::post('/register', [AuthController::class, 'register']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::delete('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });
});
