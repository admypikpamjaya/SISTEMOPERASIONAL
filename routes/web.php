<?php

use App\Http\Controllers\Asset\AssetManagementController;
use App\Http\Controllers\Asset\PublicAssetController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Report\MaintenanceReportController;
use App\Http\Controllers\User\UserManagementController;

// ADMIN COMMUNICATION & BILLING
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
    return Auth::check()
        ? redirect()->route('dashboard.index')
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

Route::middleware('auth')->get('/logout', function () {
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
        Route::get('/', fn () => view('dashboard.index'))->name('index');
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
| User Management (IT SUPPORT ONLY)
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

/*
|--------------------------------------------------------------------------
| ADMIN – COMMUNICATION & BILLING (PHASE 6 – LOCKED)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth'])
    ->group(function () {

        // ANNOUNCEMENTS
        Route::prefix('announcements')
            ->middleware('check_access:admin_announcement.read')
            ->group(function () {
                Route::get('/', [AnnouncementController::class, 'index'])
                    ->name('announcements.index');

                Route::get('/create', [AnnouncementController::class, 'create'])
                    ->middleware('check_access:admin_announcement.create')
                    ->name('announcements.create');

                Route::post('/', [AnnouncementController::class, 'store'])
                    ->middleware('check_access:admin_announcement.create')
                    ->name('announcements.store');
            });

        // BILLINGS
        Route::prefix('billings')
            ->middleware('check_access:admin_billing.read')
            ->group(function () {
                Route::get('/', [BillingController::class, 'index'])
                    ->name('billings.index');

                Route::post('/{billingId}/confirm', [BillingController::class, 'confirmPayment'])
                    ->middleware('check_access:admin_billing.confirm')
                    ->name('billings.confirm');
            });

        // REMINDERS
        Route::prefix('reminders')
            ->middleware('check_access:admin_reminder.read')
            ->group(function () {
                Route::get('/', [ReminderController::class, 'index'])
                    ->name('reminders.index');

                Route::post('/send', [ReminderController::class, 'send'])
                    ->middleware('check_access:admin_reminder.send')
                    ->name('reminders.send');
            });

        // BLAST
        Route::prefix('blast')
        
            ->middleware('check_access:admin_blast.read')
            ->group(function () {
                Route::get('/', [BlastController::class, 'index'])
                    ->name('blast.index');

                Route::post('/send', [BlastController::class, 'send'])
                    ->middleware('check_access:admin_blast.send')
                    ->name('blast.send');
                     Route::get('/blast/whatsapp', [BlastController::class, 'whatsapp'])
            ->name('blast.whatsapp');

        Route::post('/blast/whatsapp/send', [BlastController::class, 'sendWhatsapp'])
            ->name('blast.whatsapp.send');

        Route::get('/blast/email', [BlastController::class, 'email'])
            ->name('blast.email');

        Route::post('/blast/email/send', [BlastController::class, 'sendEmail'])
            ->name('blast.email.send');
            });
    });
