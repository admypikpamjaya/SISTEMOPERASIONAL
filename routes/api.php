<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\AnnouncementApiController;
use App\Http\Controllers\Api\BillingApiController;
use App\Http\Controllers\Api\ReminderApiController;
use App\Http\Controllers\Api\BlastApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| JSON ONLY – Sanctum Bearer Token
*/

// AUTH
Route::post('/login', [AuthApiController::class, 'login']);

// PROTECTED API
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthApiController::class, 'logout']);

    // Announcements
    Route::get('/announcements', [AnnouncementApiController::class, 'index']);
    Route::post('/announcements', [AnnouncementApiController::class, 'store']);

    // Billings
    Route::get('/billings', [BillingApiController::class, 'index']);
    Route::post('/billings/{billingId}/confirm', [BillingApiController::class, 'confirmPayment']);

    // Reminders
    Route::get('/reminders/preview', [ReminderApiController::class, 'preview']);
    Route::post('/reminders/send', [ReminderApiController::class, 'send']);

    // Blast
    Route::post('/blast/send', [BlastApiController::class, 'send']);
});
