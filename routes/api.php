<?php

use App\Http\Controllers\Api\V1\AdminApiController;
use App\Http\Controllers\Api\V1\AttendanceApiController;
use App\Http\Controllers\Api\V1\ScheduledNotificationApiController;
use App\Http\Controllers\Api\V1\UserApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthApiController;
use Illuminate\Support\Facades\Redis;
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
    Route::post('/register', [AuthApiController::class, 'register'])->name('apiV1.register');
    Route::post('/login', [AuthApiController::class, 'login'])->name('apiV1.login');
    Route::get('/logout', [AuthApiController::class, 'logout'])->name('apiV1.logout');
    Route::post('/email/verify', [AuthApiController::class, 'verifyEmail'])->name('apiV1.verifyEmail');
    Route::post('/forgot-password', [AuthApiController::class, 'forgotPassword'])->name('apiV1.forgotPassword');
    Route::post('/reset-password', [AuthApiController::class, 'resetPassword'])->name('apiV1.resetPassword');
    Route::get('/refresh-token', [AuthApiController::class, 'refreshToken'])->name('apiV1.refreshToken');


    Route::middleware(['auth:api'])->group(function () {

        Route::middleware(['author.admin'])->group(function (){
            Route::get('/admin/users', [AdminApiController::class, 'getUsers'])->name('apiV1.admin..getUsers');
            Route::get('/admin/user/{id}', [AdminApiController::class, 'getUser'])->name('apiV1.admin.getUser');
            Route::post('/admin/user-create', [AdminApiController::class, 'createUser'])->name('apiV1.admin.createUser');
            Route::put('/admin/user-update/{id}', [AdminApiController::class, 'updateUser'])->name('apiV1.admin.updateUser');
            Route::delete('/admin/user-delete/{id}', [AdminApiController::class, 'deleteUser'])->name('apiV1.admin.deleteUser');

            Route::resource('/admin/scheduled-notifications', ScheduledNotificationApiController::class);
        });

        Route::post('/checkin',[AttendanceApiController::class, 'checkIn'])->name('apiV1.checkIn');
        Route::get('{userId}/attendance',[AttendanceApiController::class, 'getAttendance'])->name('apiV1.getAttendance');


        Route::middleware(['ensure.user.owns'])->group(function () {
            Route::get('users/{id}', [UserApiController::class,'getUser'])->name('apiV1.getUser');
            Route::put('users/{id}/update', [UserApiController::class,'updateUser'])->name('apiV1.updateUser');
            Route::post('users/{id}/change-password', [UserApiController::class,'changePassword'])->name('apiV1.changePassword');
            Route::post('users/{id}/avatar-upload', [UserApiController::class, 'avatarUpload'])->name('apiV1.avatarUpload');
        });
    });
});
