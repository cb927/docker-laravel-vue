<?php

use App\Http\Controllers\BidController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Mechanic\MechanicProfileController;
use App\Http\Controllers\Driver\ProfileController;
use App\Http\Controllers\FulfilledJobController;
use App\Http\Controllers\FulfillJobController;
use App\Http\Controllers\Mechanic\ServiceController;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::prefix('categories')->middleware('auth:sanctum')->group(function () {
        Route::get('/', CategoryController::class);
    });

    Route::prefix('jobs')->group(function () {
        Route::post('feedback-by-driver', [FulfilledJobController::class, 'feedbackByDriver']);
        Route::post('feedback-by-mechanic', [FulfilledJobController::class, 'feedbackByMechanic']);
    });

    Route::prefix('drivers')->middleware('auth:sanctum')->group(function () {
        Route::get('{user}/profile', ProfileController::class);

        Route::prefix('jobs')->group(function () {
            Route::get('/', [JobController::class, 'index']);
            Route::post('fulfill/{job}', [FulfillJobController::class, 'store']);

            Route::prefix('fulfilled')->group(function () {
                Route::get('/', [FulfilledJobController::class, 'index']);
            });

            Route::post('/', [JobController::class, 'store']);
            Route::get('{job}', [JobController::class, 'show']);
            Route::get('active/{fulfilledJob}', [FulfilledJobController::class, 'show']);

            Route::post('{job}/accept', [FulfilledJobController::class, 'store']);

            Route::prefix('bids')->group(function () {
                Route::get('{job}', [BidController::class, 'index']);
                Route::post('{job}', [BidController::class, 'store']);
            });
        });
    });

    Route::prefix('mechanics')->middleware('auth:sanctum')->group(function () {
        Route::get('{user}/profile', MechanicProfileController::class);
        Route::get('{user}/services', [ServiceController::class, 'index']);
        Route::post('services', [ServiceController::class, 'store']);
        Route::get('{user}/fulfilled', [FulfilledJobController::class, 'getFulfilled']);
    });

    Route::prefix('mechanics')->group(function () {
        Route::get('{user}/fulfilled-for-rating', [FulfilledJobController::class, 'getFulfilledForRating']);
    });

    Route::middleware('auth:sanctum')->get('user', function () {
        return new UserResource(User::find(Auth::id()));
    });

    Route::post('password/email', [AuthController::class, 'sendPasswordResetLink']);
    Route::post('password/reset', [AuthController::class, 'callResetPassword']);
    Route::post('password/update', [AuthController::class, 'updatePassword'])->middleware('auth:sanctum');
});
