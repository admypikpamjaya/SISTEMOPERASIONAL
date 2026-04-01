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
use App\Http\Controllers\Finance\FinanceStatementController;
use App\Http\Controllers\Finance\FinanceTunggakanController;
use App\Enums\User\UserRole;

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
    if (!Auth::check()) {
        return redirect()->route('login');
    }

    if (Auth::user()->role === UserRole::BLASTING->value) {
        return redirect()->route('admin.blast.index');
    }

    return redirect()->route('dashboard.index');
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
            Route::get('/export/excel', 'exportExcel')
                ->name('export-excel');
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

        Route::delete('/report/{id}', [FinanceReportController::class, 'destroy'])
            ->middleware('check_access:finance_report.generate')
            ->name('report.destroy');

        Route::get('/report/snapshots', [FinanceReportController::class, 'snapshots'])
            ->middleware('check_access:finance_report.read')
            ->name('report.snapshots');

        Route::get('/report/balance-sheet', [FinanceStatementController::class, 'balanceSheet'])
            ->middleware('check_access:finance_balance_sheet.read')
            ->name('report.balance-sheet');

        Route::get('/report/balance-sheet/download', [FinanceStatementController::class, 'downloadBalanceSheet'])
            ->middleware('check_access:finance_balance_sheet.read')
            ->name('report.balance-sheet.download');

        Route::get('/report/profit-loss', [FinanceStatementController::class, 'profitLoss'])
            ->middleware('check_access:finance_profit_loss.read')
            ->name('report.profit-loss');

        Route::get('/report/profit-loss/download', [FinanceStatementController::class, 'downloadProfitLoss'])
            ->middleware('check_access:finance_profit_loss.read')
            ->name('report.profit-loss.download');

        Route::get('/report/general-ledger', [FinanceStatementController::class, 'generalLedger'])
            ->middleware('check_access:finance_general_ledger.read')
            ->name('report.general-ledger');

        Route::get('/report/general-ledger/manage', [FinanceStatementController::class, 'manageGeneralLedger'])
            ->middleware('check_access:finance_general_ledger.read')
            ->name('report.general-ledger.manage');

        Route::get('/report/general-ledger/download', [FinanceStatementController::class, 'downloadGeneralLedger'])
            ->middleware('check_access:finance_general_ledger.read')
            ->name('report.general-ledger.download');

        Route::post('/report/general-ledger/import', [FinanceStatementController::class, 'importGeneralLedgerExcel'])
            ->middleware('check_access:finance_report.generate')
            ->name('report.general-ledger.import');

        Route::post('/report/general-ledger/entries', [FinanceStatementController::class, 'storeGeneralLedgerEntry'])
            ->middleware('check_access:finance_report.generate')
            ->name('report.general-ledger.entries.store');

        Route::put('/report/general-ledger/entries/{entry}', [FinanceStatementController::class, 'updateGeneralLedgerEntry'])
            ->middleware('check_access:finance_report.generate')
            ->name('report.general-ledger.entries.update');

        Route::delete('/report/general-ledger/entries/{entry}', [FinanceStatementController::class, 'destroyGeneralLedgerEntry'])
            ->middleware('check_access:finance_report.generate')
            ->name('report.general-ledger.entries.destroy');

        Route::get('/report/journal-items', [FinanceStatementController::class, 'journalItems'])
            ->middleware('check_access:finance_general_ledger.read')
            ->name('report.journal-items');

        Route::get('/report/journal-items/download', [FinanceStatementController::class, 'downloadJournalItems'])
            ->middleware('check_access:finance_general_ledger.read')
            ->name('report.journal-items.download');

        Route::post('/report/account-mapping', [FinanceStatementController::class, 'saveAccountMapping'])
            ->middleware('check_access:finance_report.generate')
            ->name('report.account-mapping');

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

                Route::post('/publish-all-draft', [FinanceInvoiceController::class, 'publishAllDraft'])
                    ->middleware('check_access:finance_invoice.update')
                    ->name('publish-all-draft');

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

        Route::prefix('tunggakan')
            ->name('tunggakan.')
            ->group(function () {
                Route::get('/', [FinanceTunggakanController::class, 'index'])
                    ->middleware('check_access:finance_report.read')
                    ->name('index');

                Route::get('/version', [FinanceTunggakanController::class, 'version'])
                    ->middleware('check_access:finance_report.read')
                    ->name('version');

                Route::post('/manual', [FinanceTunggakanController::class, 'storeManual'])
                    ->middleware('check_access:finance_report.generate')
                    ->name('manual.store');

                Route::post('/import', [FinanceTunggakanController::class, 'importExcel'])
                    ->middleware('check_access:finance_report.generate')
                    ->name('import');

                Route::post('/sync-db', [FinanceTunggakanController::class, 'syncDatabase'])
                    ->middleware('check_access:finance_report.generate')
                    ->name('sync-db');

                Route::post('/template-default', [FinanceTunggakanController::class, 'createDefaultTemplate'])
                    ->middleware('check_access:finance_report.generate')
                    ->name('template-default');

                Route::post('/blast-whatsapp', [FinanceTunggakanController::class, 'blastWhatsapp'])
                    ->middleware('check_access:finance_report.generate')
                    ->name('blast-whatsapp');

                Route::delete('/delete-all', [FinanceTunggakanController::class, 'destroyAll'])
                    ->middleware('check_access:finance_report.generate')
                    ->name('destroy-all');

                Route::put('/{record}', [FinanceTunggakanController::class, 'update'])
                    ->whereUuid('record')
                    ->middleware('check_access:finance_report.generate')
                    ->name('update');

                Route::delete('/{record}', [FinanceTunggakanController::class, 'destroy'])
                    ->whereUuid('record')
                    ->middleware('check_access:finance_report.generate')
                    ->name('destroy');
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
                Route::get('/whatsapp/manage-phone', [BlastController::class, 'whatsappManagePhone'])
                    ->name('whatsapp.manage');
                Route::post('/whatsapp/send', [BlastController::class, 'sendWhatsapp'])
                    ->middleware('check_access:admin_blast.send')
                    ->name('whatsapp.send');
                Route::get('/whatsapp/gateway-status', [BlastController::class, 'whatsappGatewayStatus'])
                    ->name('whatsapp.gateway-status');
                Route::post('/whatsapp/gateway-reconnect', [BlastController::class, 'whatsappGatewayReconnect'])
                    ->name('whatsapp.gateway-reconnect');
                Route::get('/whatsapp/gateway-devices', [BlastController::class, 'whatsappGatewayDevices'])
                    ->name('whatsapp.gateway-devices');
                Route::post('/whatsapp/gateway-devices', [BlastController::class, 'whatsappGatewayDeviceCreate'])
                    ->name('whatsapp.gateway-devices.create');
                Route::post('/whatsapp/gateway-devices/{deviceId}/connect', [BlastController::class, 'whatsappGatewayDeviceConnect'])
                    ->name('whatsapp.gateway-devices.connect');
                Route::post('/whatsapp/gateway-devices/{deviceId}/activate', [BlastController::class, 'whatsappGatewayDeviceActivate'])
                    ->name('whatsapp.gateway-devices.activate');
                Route::post('/whatsapp/gateway-devices/{deviceId}/reconnect', [BlastController::class, 'whatsappGatewayDeviceReconnect'])
                    ->name('whatsapp.gateway-devices.reconnect');
                Route::post('/whatsapp/gateway-devices/{deviceId}/disconnect', [BlastController::class, 'whatsappGatewayDeviceDisconnect'])
                    ->name('whatsapp.gateway-devices.disconnect');
                Route::post('/whatsapp/gateway-devices/{deviceId}/rename', [BlastController::class, 'whatsappGatewayDeviceRename'])
                    ->name('whatsapp.gateway-devices.rename');
                Route::post('/whatsapp/gateway-devices/reset', [BlastController::class, 'whatsappGatewayDevicesReset'])
                    ->name('whatsapp.gateway-devices.reset');
                Route::delete('/whatsapp/gateway-devices/{deviceId}', [BlastController::class, 'whatsappGatewayDeviceDelete'])
                    ->name('whatsapp.gateway-devices.delete');
                Route::get('/whatsapp/provider-status', [BlastController::class, 'whatsappProviderStatus'])
                    ->name('whatsapp.provider-status');
                Route::post('/whatsapp/provider-update', [BlastController::class, 'whatsappProviderUpdate'])
                    ->name('whatsapp.provider-update');

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
                    Route::get('/employees', [BlastRecipientController::class, 'employeeIndex'])
                        ->name('employees.index');
                    Route::get('/employees/create', [BlastRecipientController::class, 'employeeCreate'])
                        ->middleware('check_access:blast_recipient.create')
                        ->name('employees.create');
                    Route::post('/employees', [BlastRecipientController::class, 'employeeStore'])
                        ->middleware('check_access:blast_recipient.create')
                        ->name('employees.store');
                    Route::get('/employees/{id}/edit', [BlastRecipientController::class, 'employeeEdit'])
                        ->middleware('check_access:blast_recipient.update')
                        ->name('employees.edit');
                    Route::put('/employees/{id}', [BlastRecipientController::class, 'employeeUpdate'])
                        ->middleware('check_access:blast_recipient.update')
                        ->name('employees.update');
                    Route::get('/employees-ypik', [BlastRecipientController::class, 'employeeYpikIndex'])
                        ->name('employees-ypik.index');
                    Route::get('/employees-ypik-pamjaya', [BlastRecipientController::class, 'employeeYpikPamJayaIndex'])
                        ->name('employees-ypik-pamjaya.index');
                    Route::get('/employees-ypik/create', [BlastRecipientController::class, 'employeeYpikCreate'])
                        ->middleware('check_access:blast_recipient.create')
                        ->name('employees-ypik.create');
                    Route::post('/employees-ypik', [BlastRecipientController::class, 'employeeYpikStore'])
                        ->middleware('check_access:blast_recipient.create')
                        ->name('employees-ypik.store');
                    Route::get('/employees-ypik/{id}/edit', [BlastRecipientController::class, 'employeeYpikEdit'])
                        ->middleware('check_access:blast_recipient.update')
                        ->name('employees-ypik.edit');
                    Route::put('/employees-ypik/{id}', [BlastRecipientController::class, 'employeeYpikUpdate'])
                        ->middleware('check_access:blast_recipient.update')
                        ->name('employees-ypik.update');
                    Route::post('/employees-ypik/import', [BlastRecipientController::class, 'importEmployeeYpik'])
                        ->middleware('check_access:blast_recipient.import')
                        ->name('employees-ypik.import');
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
                    Route::delete('/employees/{id}', [BlastRecipientController::class, 'destroyEmployee'])
                        ->middleware('check_access:blast_recipient.delete')
                        ->name('employees.destroy');
                    Route::delete('/employees/delete-all', [BlastRecipientController::class, 'destroyAllEmployees'])
                        ->middleware('check_access:blast_recipient.delete')
                        ->name('employees.destroy-all');
                    Route::delete('/employees/bulk-delete', [BlastRecipientController::class, 'destroySelectedEmployees'])
                        ->middleware('check_access:blast_recipient.delete')
                        ->name('employees.bulk-delete');
                    Route::delete('/employees-ypik/{id}', [BlastRecipientController::class, 'destroyEmployeeYpik'])
                        ->middleware('check_access:blast_recipient.delete')
                        ->name('employees-ypik.destroy');
                    Route::delete('/employees-ypik/delete-all', [BlastRecipientController::class, 'destroyAllEmployeesYpik'])
                        ->middleware('check_access:blast_recipient.delete')
                        ->name('employees-ypik.destroy-all');
                    Route::delete('/employees-ypik-pamjaya/delete-all', [BlastRecipientController::class, 'destroyAllEmployeesYpikPamJaya'])
                        ->middleware('check_access:blast_recipient.delete')
                        ->name('employees-ypik-pamjaya.destroy-all');
                    Route::delete('/employees-ypik/bulk-delete', [BlastRecipientController::class, 'destroySelectedEmployeesYpik'])
                        ->middleware('check_access:blast_recipient.delete')
                        ->name('employees-ypik.bulk-delete');
                    Route::delete('/delete-all', [BlastRecipientController::class, 'destroyAllStudents'])
                        ->middleware('check_access:blast_recipient.delete')
                        ->name('destroy-all');
                    Route::delete('/bulk-delete', [BlastRecipientController::class, 'destroySelectedStudents'])
                        ->middleware('check_access:blast_recipient.delete')
                        ->name('bulk-delete');
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
