<?php

use App\Http\Controllers\Api\V1\UserApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::group([
    'middleware' => 'api',
    'prefix' => 'v1'
], function () {
    Route::get('/users', [AuthApiController::class, 'getUsers'])->name('apiV1.getUsers');
    Route::post('/register', [AuthApiController::class, 'register'])->name('apiV1.register');
    Route::post('/login', [AuthApiController::class, 'login'])->name('apiV1.login');
    Route::get('/logout', [AuthApiController::class, 'logout'])->name('apiV1.logout');
    Route::post('/email/verify', [AuthApiController::class, 'verifyEmail'])->name('apiV1.verifyEmail');
    Route::post('/forgot-password', [AuthApiController::class, 'forgotPassword'])->name('apiV1.forgotPassword');
    Route::post('/reset-password', [AuthApiController::class, 'resetPassword'])->name('apiV1.resetPassword');
    Route::post('/refresh-token', [AuthApiController::class, 'refreshToken'])->name('apiV1.refreshToken');

    Route::get('user/{id}', [UserApiController::class,'getUser'])->name('apiV1.getUser');


    Route::get('/test', function () {
        return apiResponse(__('This is a protected resource'), null, 200);
    })->middleware('auth:api');
});
