<?php

use App\Http\Controllers\Auth\LoginController;
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

Route::prefix('asset-management')->name('asset-management.')->middleware(['auth', 'check_access:asset_management.read'])->group(function() {
    Route::get('/', function() {
        return 'Asset Management';
    })->name('index');
});

Route::prefix('maintenance-report')->name('maintenance-report.')->middleware(['auth', 'check_access:maintenance_report.read'])->group(function() {
    Route::get('/', function() {
        return 'Maintenance Report';
    })->name('index');
});

Route::prefix('user-database')->name('user-database.')->middleware(['auth', 'check_access:user_management.read'])->group(function() {
    Route::get('/', function() {
        return 'User Database';
    })->name('index');
});