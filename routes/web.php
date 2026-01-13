<?php

use App\Http\Controllers\Asset\AssetManagementController;
use App\Http\Controllers\Asset\PublicAssetController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Report\MaintenanceReportController;
use App\Http\Controllers\User\UserManagementController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return (Auth::check()) ?  redirect()->intended(route('dashboard.index')) : redirect()->route('login');
});

Route::prefix('login')->middleware('guest')->controller(LoginController::class)->group(function () {
    Route::get('/', 'index')->name('login');
    Route::post('/', 'authenticate');
});

Route::middleware('auth')->get('logout', function() {
    Auth::logout();
    return redirect()->route('login');
})->name('logout');

Route::prefix('dashboard')->name('dashboard.')->middleware('auth')->group(function() {
    Route::get('/', function() {
        return view('dashboard.index');
    })->name('index');
});

Route::prefix('asset-management')->name('asset-management.')->middleware(['auth', 'check_access:asset_management.read'])->controller(AssetManagementController::class)->group(function() {
    Route::get('/', 'index')->name('index');
    Route::get('/register', 'showRegisterForm')->name('register-form');
    Route::get('/edit/{id}', 'showEditForm')->name('edit-form');
    Route::get('/download-qr-code', 'downloadQrCode')->name('download-qr-code');

    Route::post('/', 'store')->name('store');
    Route::post('/file', 'storeWithFile')->name('store-with-file');

    Route::put('/', 'update')->name('update');

    Route::delete('/{id}', 'delete')->name('delete');
});

Route::prefix('maintenance-report')->name('maintenance-report.')->controller(MaintenanceReportController::class)->group(function() {
    Route::middleware(['auth', 'check_access:maintenance_report.read'])->group(function() {
        Route::get('/', 'index')->name('index');
        Route::get('/{id}', 'show')->name('detail');      
        
        Route::put('/', 'update')->middleware('check_access:maintenance_report.update')->name('update');
        Route::put('/update/status', 'updateStatus')->middleware('check_access:maintenance_report.update_status')->name('update-status');

        Route::delete('/{id}', 'delete')->middleware('check_access:maintenance_report.delete')->name('delete');
    });

    Route::post('/submit', 'store')->name('submit');
});

Route::prefix('user-database')->name('user-database.')->middleware(['auth', 'check_access:user_management.read'])->controller(UserManagementController::class)->group(function() {
    Route::get('/', 'index')->name('index');
    Route::get('/{id}', 'show')->name('show');

    Route::post('/', 'store')->name('store');
    Route::post('/reset-password/{id}', 'sendResetPasswordLink')->name('send-reset-password-link');
    Route::put('/', 'update')->name('update');

    Route::delete('/{id}', 'delete')->name('delete');
});

// Public View
Route::get('assets/{id}', [PublicAssetController::class, 'show'])->name('assets.detail');
Route::get('reset-password/{token}', [ResetPasswordController::class, 'index'])->name('password.reset');
Route::post('reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');