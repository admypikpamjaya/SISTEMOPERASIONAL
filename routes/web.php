<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DiscussionController;

use App\Http\Controllers\Asset\AssetManagementController;
use App\Http\Controllers\Asset\PublicAssetController;

use App\Http\Controllers\Report\MaintenanceReportController;
use App\Http\Controllers\User\UserManagementController;
use App\Http\Controllers\Finance\AssetDepreciationController;
use App\Http\Controllers\Finance\FinanceAccountController;
use App\Http\Controllers\Finance\FinanceDashboardController;
use App\Http\Controllers\Finance\FinanceInvoiceController;
use App\Http\Controllers\Finance\FinanceReportController;

// ADMIN
use App\Http\Controllers\Admin\AnnouncementController;
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
        Route::get('/', [DashboardController::class, 'index'])->name('index');
        Route::get('/chart-data', [DashboardController::class, 'chartData'])->name('chart-data');
    });

/*
|--------------------------------------------------------------------------
| Discussion
|--------------------------------------------------------------------------
*/
Route::prefix('discussion')
    ->name('discussion.')
    ->middleware('auth')
    ->controller(DiscussionController::class)
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/messages', 'messages')->name('messages');
        Route::post('/messages', 'store')->name('messages.store');
        Route::post('/messages/{message}/pin', 'pin')->name('messages.pin');
        Route::delete('/messages/{message}', 'destroy')->name('messages.destroy');
        Route::get('/messages/{message}/voice-note', 'voiceNote')->name('messages.voice-note');
        Route::get('/messages/{message}/attachment', 'attachment')->name('messages.attachment');
        Route::get('/messages/{message}/attachment-preview', 'attachmentPreview')->name('messages.attachment-preview');
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
        Route::get('/register', 'showRegisterForm')
            ->middleware('check_access:asset_management.write')
            ->name('register-form');
        Route::get('/edit/{id}', 'showEditForm')
            ->middleware('check_access:asset_management.update')
            ->name('edit-form');
        Route::get('/download-qr-code', 'downloadQrCode')->name('download-qr-code');
        Route::post('/', 'store')
            ->middleware('check_access:asset_management.write')
            ->name('store');
        Route::post('/file', 'storeWithFile')
            ->middleware('check_access:asset_management.write')
            ->name('store-with-file');
        Route::put('/', 'update')
            ->middleware('check_access:asset_management.update')
            ->name('update');
        Route::delete('/bulk', 'bulkDelete')
            ->middleware('check_access:asset_management.delete')
            ->name('bulk-delete');
        Route::delete('/{id}', 'delete')
            ->middleware('check_access:asset_management.delete')
            ->name('delete');
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
        Route::post('/', 'store')
            ->middleware('check_access:user_management.write')
            ->name('store');
        Route::post('/reset-password/{id}', 'sendResetPasswordLink')
            ->middleware('check_access:user_management.update')
            ->name('send-reset-password-link');
        Route::put('/', 'update')
            ->middleware('check_access:user_management.update')
            ->name('update');
        Route::delete('/{id}', 'delete')
            ->middleware('check_access:user_management.delete')
            ->name('delete');
    });

/*
|--------------------------------------------------------------------------
| Finance
|--------------------------------------------------------------------------
*/
Route::prefix('finance')
    ->name('finance.')
    ->middleware(['auth', 'ensure_finance_access'])
    ->group(function () {
        Route::get('/dashboard', [FinanceDashboardController::class, 'index'])
            ->middleware('check_access:finance_report.read')
            ->name('dashboard');

        Route::get('/depreciation', [AssetDepreciationController::class, 'index'])
            ->middleware('check_access:finance_depreciation.read')
            ->name('depreciation.index');

        Route::post('/depreciation/calc', [AssetDepreciationController::class, 'calculate'])
            ->middleware('check_access:finance_depreciation.calculate')
            ->name('depreciation.calc');

        Route::get('/depreciation/logs/{log}', [AssetDepreciationController::class, 'showLog'])
            ->middleware('check_access:finance_depreciation.read')
            ->name('depreciation.logs.show');

        Route::get('/depreciation/logs/{log}/download', [AssetDepreciationController::class, 'downloadLogPdf'])
            ->middleware('check_access:finance_depreciation.read')
            ->name('depreciation.logs.download');

        Route::get('/report', [FinanceReportController::class, 'index'])
            ->middleware('check_access:finance_report.read')
            ->name('report.index');

        Route::post('/report', [FinanceReportController::class, 'store'])
            ->middleware('check_access:finance_report.generate')
            ->name('report.store');

        Route::get('/report/{id}/edit', [FinanceReportController::class, 'edit'])
            ->middleware('check_access:finance_report.generate')
            ->name('report.edit');

        Route::put('/report/{id}', [FinanceReportController::class, 'update'])
            ->middleware('check_access:finance_report.generate')
            ->name('report.update');

        Route::get('/report/snapshots', [FinanceReportController::class, 'snapshots'])
            ->middleware('check_access:finance_report.read')
            ->name('report.snapshots');

        Route::get('/report/{id}/download', [FinanceReportController::class, 'download'])
            ->middleware('check_access:finance_report.read')
            ->name('report.download');

        Route::get('/report/{id}', [FinanceReportController::class, 'show'])
            ->middleware('check_access:finance_report.read')
            ->name('report.show');

        Route::prefix('accounts')
            ->name('accounts.')
            ->group(function () {
                Route::get('/', [FinanceAccountController::class, 'index'])
                    ->middleware('check_access:finance_report.read')
                    ->name('index');

                Route::post('/', [FinanceAccountController::class, 'store'])
                    ->middleware('check_access:finance_report.generate')
                    ->name('store');

                Route::put('/{account}', [FinanceAccountController::class, 'update'])
                    ->middleware('check_access:finance_report.generate')
                    ->name('update');

                Route::delete('/classifications/{classNo}', [FinanceAccountController::class, 'destroyClassification'])
                    ->middleware('check_access:finance_report.generate')
                    ->name('classifications.destroy');
            });

        Route::prefix('invoices')
            ->name('invoice.')
            ->group(function () {
                Route::get('/', [FinanceInvoiceController::class, 'index'])
                    ->middleware('check_access:finance_invoice.read')
                    ->name('index');

                Route::get('/create', [FinanceInvoiceController::class, 'create'])
                    ->middleware('check_access:finance_invoice.create')
                    ->name('create');

                Route::post('/', [FinanceInvoiceController::class, 'store'])
                    ->middleware('check_access:finance_invoice.create')
                    ->name('store');

                Route::get('/{invoice}/download', [FinanceInvoiceController::class, 'download'])
                    ->middleware('check_access:finance_invoice.read')
                    ->name('download');

                Route::get('/{invoice}', [FinanceInvoiceController::class, 'show'])
                    ->middleware('check_access:finance_invoice.read')
                    ->name('show');

                Route::get('/{invoice}/edit', [FinanceInvoiceController::class, 'edit'])
                    ->middleware('check_access:finance_invoice.update')
                    ->name('edit');

                Route::put('/{invoice}', [FinanceInvoiceController::class, 'update'])
                    ->middleware('check_access:finance_invoice.update')
                    ->name('update');

                Route::delete('/{invoice}', [FinanceInvoiceController::class, 'destroy'])
                    ->middleware('check_access:finance_invoice.delete')
                    ->name('destroy');

                Route::post('/{invoice}/post', [FinanceInvoiceController::class, 'post'])
                    ->middleware('check_access:finance_invoice.update')
                    ->name('post');

                Route::post('/{invoice}/set-draft', [FinanceInvoiceController::class, 'setDraft'])
                    ->middleware('check_access:finance_invoice.update')
                    ->name('set-draft');

                Route::post('/{invoice}/notes', [FinanceInvoiceController::class, 'storeNote'])
                    ->middleware('check_access:finance_invoice.note')
                    ->name('notes.store');
            });
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
                Route::get('/{id}/edit', [AnnouncementController::class, 'edit'])
                    ->middleware('check_access:admin_announcement.create')
                    ->name('announcements.edit');
                Route::put('/{id}', [AnnouncementController::class, 'update'])
                    ->middleware('check_access:admin_announcement.create')
                    ->name('announcements.update');
                Route::delete('/{id}', [AnnouncementController::class, 'destroy'])
                    ->middleware('check_access:admin_announcement.create')
                    ->name('announcements.destroy');
            });

        /* ================= REMINDERS ================= */
        Route::prefix('reminders')
            ->name('reminders.')
            ->middleware('check_access:admin_reminder.read')
            ->group(function () {
                Route::get('/', [ReminderController::class, 'index'])
                    ->name('index');
                Route::get('/alerts', [ReminderController::class, 'alerts'])
                    ->name('alerts');
                Route::post('/', [ReminderController::class, 'store'])
                    ->middleware('check_access:admin_reminder.send')
                    ->name('store');
                Route::get('/{reminder}/edit', [ReminderController::class, 'edit'])
                    ->middleware('check_access:admin_reminder.send')
                    ->name('edit');
                Route::put('/{reminder}', [ReminderController::class, 'update'])
                    ->middleware('check_access:admin_reminder.send')
                    ->name('update');
                Route::post('/send', [ReminderController::class, 'store'])
                    ->middleware('check_access:admin_reminder.send')
                    ->name('send');
                Route::post('/{reminder}/toggle', [ReminderController::class, 'toggle'])
                    ->middleware('check_access:admin_reminder.send')
                    ->name('toggle');
            });

        /* ================= BLAST ================= */
        Route::prefix('blast')
            ->name('blast.')
            ->middleware('check_access:admin_blast.read')
            ->group(function () {

                Route::get('/', [BlastController::class, 'index'])->name('index');

                // WhatsApp
                Route::get('/whatsapp', [BlastController::class, 'whatsapp'])->name('whatsapp');
                Route::post('/whatsapp/send', [BlastController::class, 'sendWhatsapp'])
                    ->middleware('check_access:admin_blast.send')
                    ->name('whatsapp.send');

                // Email
                Route::get('/email', [BlastController::class, 'email'])->name('email');
                Route::post('/email/send', [BlastController::class, 'sendEmail'])
                    ->middleware('check_access:admin_blast.send')
                    ->name('email.send');
                Route::get('/activity-api', [BlastController::class, 'activity'])->name('activity');
                Route::post('/activity/clear', [BlastController::class, 'clearActivityLogs'])
                    ->middleware('check_access:admin_blast.send')
                    ->name('activity.clear');
                Route::post('/activity/delete', [BlastController::class, 'deleteActivityLog'])
                    ->middleware('check_access:admin_blast.send')
                    ->name('activity.delete');
                Route::post('/activity/retry', [BlastController::class, 'retryActivityLog'])
                    ->middleware('check_access:admin_blast.send')
                    ->name('activity.retry');
                Route::get('/campaign-api', [BlastController::class, 'campaigns'])->name('campaigns');
                Route::post('/campaign/pause', [BlastController::class, 'pauseCampaign'])
                    ->middleware('check_access:admin_blast.send')
                    ->name('campaign.pause');
                Route::post('/campaign/resume', [BlastController::class, 'resumeCampaign'])
                    ->middleware('check_access:admin_blast.send')
                    ->name('campaign.resume');
                Route::post('/campaign/stop', [BlastController::class, 'stopCampaign'])
                    ->middleware('check_access:admin_blast.send')
                    ->name('campaign.stop');

                /* ===== RECIPIENT CRUD ===== */
                Route::prefix('recipients')
                    ->name('recipients.')
                    ->middleware('check_access:blast_recipient.read')
                    ->group(function () {
                    Route::get('/', [BlastRecipientController::class, 'index'])->name('index');
                    Route::get('/create', [BlastRecipientController::class, 'create'])
                        ->middleware('check_access:blast_recipient.create')
                        ->name('create');
                    Route::post('/', [BlastRecipientController::class, 'store'])
                        ->middleware('check_access:blast_recipient.create')
                        ->name('store');
                    Route::get('/{id}/edit', [BlastRecipientController::class, 'edit'])
                        ->middleware('check_access:blast_recipient.update')
                        ->name('edit');
                    Route::put('/{id}', [BlastRecipientController::class, 'update'])
                        ->middleware('check_access:blast_recipient.update')
                        ->name('update');
                    Route::post('/import', [BlastRecipientController::class, 'import'])
                        ->middleware('check_access:blast_recipient.import')
                        ->name('import');
                    Route::delete('/{id}', [BlastRecipientController::class, 'destroy'])
                        ->middleware('check_access:blast_recipient.delete')
                        ->name('destroy');
                });

                /* ===== RECIPIENT API (JSON) ===== */
                Route::get('/recipients-api', [BlastController::class, 'recipients']);

                /* ===== TEMPLATES (PHASE 10 – FIXED) ===== */
                Route::prefix('templates')
                    ->middleware('check_access:blast_template.read')
                    ->group(function () {
                        Route::get('/', [BlastMessageTemplateController::class, 'index'])
                            ->name('templates.index');
                        Route::get('/create', [BlastMessageTemplateController::class, 'create'])
                            ->middleware('check_access:blast_template.create')
                            ->name('templates.create');
                        Route::post('/', [BlastMessageTemplateController::class, 'store'])
                            ->middleware('check_access:blast_template.create')
                            ->name('templates.store');
                        Route::get('/{id}/edit', [BlastMessageTemplateController::class, 'edit'])
                            ->middleware('check_access:blast_template.update')
                            ->name('templates.edit');
                        Route::put('/{id}', [BlastMessageTemplateController::class, 'update'])
                            ->middleware('check_access:blast_template.update')
                            ->name('templates.update');
                        Route::delete('/{id}', [BlastMessageTemplateController::class, 'destroy'])
                            ->middleware('check_access:blast_template.delete')
                            ->name('templates.destroy');
                    });
            });
    });
