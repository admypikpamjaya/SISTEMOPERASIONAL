<?php

use App\Http\Controllers\Asset\AssetManagementController;
use App\Http\Controllers\Asset\PublicAssetController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Report\MaintenanceReportController;
use App\Http\Controllers\User\UserManagementController;

// === ADMIN COMMUNICATION & BILLING (PHASE 6.1 – WEB ONLY) ===
use App\Http\Controllers\Admin\AnnouncementController;
use App\Http\Controllers\Admin\BillingController;
use App\Http\Controllers\Admin\ReminderController;
use App\Http\Controllers\Admin\BlastController;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return (Auth::check())
        ? redirect()->intended(route('dashboard.index'))
        : redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/
Route::prefix('login')
    ->middleware('guest')
    ->controller(LoginController::class)
    ->group(function () {
        Route::get('/', 'index')->name('login');
        Route::post('/', 'authenticate');
    });

Route::middleware('auth')->get('logout', function () {
    Auth::logout();
    return redirect()->route('login');
})->name('logout');

/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
*/
Route::prefix('dashboard')
    ->name('dashboard.')
    ->middleware('auth')
    ->group(function () {
        Route::get('/', function () {
            return view('dashboard.index');
        })->name('index');
    });

/*
|--------------------------------------------------------------------------
| Asset Management
|--------------------------------------------------------------------------
*/
Route::prefix('asset-management')
    ->name('asset-management.')
    ->middleware(['auth', 'check_access:asset_management.read'])
    ->controller(AssetManagementController::class)
    ->group(function () {

        Route::get('/', 'index')->name('index');
        Route::get('/register', 'showRegisterForm')->name('register-form');
        Route::get('/edit/{id}', 'showEditForm')->name('edit-form');
        Route::get('/download-qr-code', 'downloadQrCode')->name('download-qr-code');

        Route::post('/', 'store')->name('store');
        Route::post('/file', 'storeWithFile')->name('store-with-file');

        Route::put('/', 'update')->name('update');
        Route::delete('/{id}', 'delete')->name('delete');
    });

/*
|--------------------------------------------------------------------------
| Maintenance Report
|--------------------------------------------------------------------------
*/
Route::prefix('maintenance-report')
    ->name('maintenance-report.')
    ->controller(MaintenanceReportController::class)
    ->group(function () {

        Route::middleware(['auth', 'check_access:maintenance_report.read'])->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/{id}', 'show')->name('detail');

            Route::put('/', 'update')
                ->middleware('check_access:maintenance_report.update')
                ->name('update');

            Route::put('/update/status', 'updateStatus')
                ->middleware('check_access:maintenance_report.update_status')
                ->name('update-status');

            Route::delete('/{id}', 'delete')
                ->middleware('check_access:maintenance_report.delete')
                ->name('delete');
        });

        Route::post('/submit', 'store')->name('submit');
    });

/*
|--------------------------------------------------------------------------
| User Management
|--------------------------------------------------------------------------
*/
Route::prefix('user-database')
    ->name('user-database.')
    ->middleware(['auth', 'check_access:user_management.read'])
    ->controller(UserManagementController::class)
    ->group(function () {

        Route::get('/', 'index')->name('index');
        Route::get('/{id}', 'show')->name('show');

        Route::post('/', 'store')->name('store');
        Route::post('/reset-password/{id}', 'sendResetPasswordLink')
            ->name('send-reset-password-link');

        Route::put('/', 'update')->name('update');
        Route::delete('/{id}', 'delete')->name('delete');
    });

/*
|--------------------------------------------------------------------------
| Public View
|--------------------------------------------------------------------------
*/
Route::get('assets/{id}', [PublicAssetController::class, 'show'])
    ->name('assets.detail');

Route::get('reset-password/{token}', [ResetPasswordController::class, 'index'])
    ->name('password.reset');

Route::post('reset-password', [ResetPasswordController::class, 'reset'])
    ->name('password.update');


Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'check_access:admin_communication.read'])
    ->group(function () {

        // ANNOUNCEMENTS
        Route::get('/announcements', [AnnouncementController::class, 'index'])
            ->name('announcements.index');
        Route::get('/announcements/create', [AnnouncementController::class, 'create'])
            ->name('announcements.create');
        Route::post('/announcements', [AnnouncementController::class, 'store'])
            ->name('announcements.store');

        // BILLINGS
        // Billings
Route::get('/billings', [BillingController::class, 'index']);
Route::post('/billings/{billingId}/confirm', [BillingController::class, 'confirmPayment']);


        // REMINDERS
        Route::get('/reminders/preview', [ReminderController::class, 'preview'])
            ->name('reminders.preview');
        Route::post('/reminders/send', [ReminderController::class, 'send'])
            ->name('reminders.send');

        // BLAST
        Route::get('/blast/create', [BlastController::class, 'create'])
            ->name('blast.create');
        Route::post('/blast/send', [BlastController::class, 'send'])
            ->name('blast.send');
    });
/*
|-------------------------------------------------------------------------- 
| ADMIN – COMMUNICATION & BILLING (PHASE 6.2.2)
|-------------------------------------------------------------------------- 
| STATUS:
| ✔ Sidebar bisa diklik
| ✔ Halaman aktif
| ✔ Tanpa logic bisnis
| ✔ WEB ONLY
*/
Route::prefix('admin')
    ->middleware(['auth',])
    ->name('admin.')
    ->group(function () {

        Route::get('/announcements', [AnnouncementController::class, 'index'])
            ->name('announcements.index');

        Route::get('/billings', [BillingController::class, 'index'])
            ->name('billings.index');

        Route::get('/reminders', [ReminderController::class, 'index'])
            ->name('reminders.index');

        Route::get('/blast', [BlastController::class, 'index'])
            ->name('blast.index');
    });
