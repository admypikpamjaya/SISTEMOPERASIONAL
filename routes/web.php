<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ResetPasswordController;

use App\Http\Controllers\Asset\AssetManagementController;
use App\Http\Controllers\Asset\PublicAssetController;

use App\Http\Controllers\Report\MaintenanceReportController;
use App\Http\Controllers\User\UserManagementController;

// ADMIN
use App\Http\Controllers\Admin\AnnouncementController;
use App\Http\Controllers\Admin\BillingController;
use App\Http\Controllers\Admin\ReminderController;
use App\Http\Controllers\Admin\BlastController;
use App\Http\Controllers\Admin\BlastRecipientController;
use App\Http\Controllers\Admin\BlastMessageTemplateController;


/*
|--------------------------------------------------------------------------
| Root
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
        Route::delete('/bulk', 'bulkDelete')->name('bulk-delete');
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
| Public
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
| ADMIN AREA
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->name('admin.')
    ->middleware('auth')
    ->group(function () {

        /* ================= ANNOUNCEMENTS ================= */
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

        /* ================= BILLINGS ================= */
        Route::prefix('billings')
            ->middleware('check_access:admin_billing.read')
            ->group(function () {

                Route::get('/', [BillingController::class, 'index'])
                    ->name('billings.index');

                Route::post('/{billingId}/confirm', [BillingController::class, 'confirmPayment'])
                    ->middleware('check_access:admin_billing.confirm')
                    ->name('billings.confirm');
            });

        /* ================= REMINDERS ================= */
        Route::prefix('reminders')
            ->middleware('check_access:admin_reminder.read')
            ->group(function () {

                Route::get('/', [ReminderController::class, 'index'])
                    ->name('reminders.index');

                Route::post('/send', [ReminderController::class, 'send'])
                    ->middleware('check_access:admin_reminder.send')
                    ->name('reminders.send');
            });

        /* ================= BLAST ================= */
        Route::prefix('blast')
            ->name('blast.')
            ->middleware('check_access:admin_blast.read')
            ->group(function () {

                Route::get('/', [BlastController::class, 'index'])->name('index');

                Route::get('/whatsapp', [BlastController::class, 'whatsapp'])->name('whatsapp');
                Route::post('/whatsapp/send', [BlastController::class, 'sendWhatsapp'])->name('whatsapp.send');

                Route::get('/email', [BlastController::class, 'email'])->name('email');
                Route::post('/email/send', [BlastController::class, 'sendEmail'])->name('email.send');

                /* ===== RECIPIENTS (PHASE 9) ===== */
                Route::prefix('recipients')->name('recipients.')->group(function () {

                    Route::get('/', [BlastRecipientController::class, 'index'])->name('index');
                    Route::get('/create', [BlastRecipientController::class, 'create'])->name('create');
                    Route::post('/', [BlastRecipientController::class, 'store'])->name('store');

                    Route::get('/{id}/edit', [BlastRecipientController::class, 'edit'])->name('edit');
                    Route::put('/{id}', [BlastRecipientController::class, 'update'])->name('update');

                    Route::post('/import', [BlastRecipientController::class, 'import'])->name('import');
                    Route::delete('/{id}', [BlastRecipientController::class, 'destroy'])->name('destroy');
                });

                /* ===== TEMPLATES (PHASE 10) ===== */
               Route::prefix('blast/templates')
    ->middleware('check_access:blast_template.read')
    ->group(function () {

        Route::get('/', [BlastMessageTemplateController::class, 'index'])
            ->name('blast.templates.index');

        Route::get('/create', [BlastMessageTemplateController::class, 'create'])
            ->middleware('check_access:blast_template.create')
            ->name('blast.templates.create');

        Route::post('/', [BlastMessageTemplateController::class, 'store'])
            ->middleware('check_access:blast_template.create')
            ->name('blast.templates.store');

        Route::get('/{id}/edit', [BlastMessageTemplateController::class, 'edit'])
            ->middleware('check_access:blast_template.update')
            ->name('blast.templates.edit');

        Route::put('/{id}', [BlastMessageTemplateController::class, 'update'])
            ->middleware('check_access:blast_template.update')
            ->name('blast.templates.update');

        Route::delete('/{id}', [BlastMessageTemplateController::class, 'destroy'])
            ->middleware('check_access:blast_template.delete')
            ->name('blast.templates.destroy');
                });
            });
    });
