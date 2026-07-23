<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AnnouncementTrackingController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DiscussionController;
use App\Http\Controllers\SystemManagementController;

use App\Http\Controllers\Asset\AssetManagementController;
use App\Http\Controllers\Asset\PublicAssetController;

use App\Http\Controllers\Report\MaintenanceReportController;
use App\Http\Controllers\Report\MaintenanceNotificationRecipientController;
use App\Http\Controllers\User\UserManagementController;
use App\Http\Controllers\Finance\AssetDepreciationController;
use App\Http\Controllers\Finance\FinanceAccountController;
use App\Http\Controllers\Finance\FinanceDashboardController;
use App\Http\Controllers\Finance\FinanceCategoryController;
use App\Http\Controllers\Finance\FinanceInvoiceController;
use App\Http\Controllers\Finance\FinanceReportController;
use App\Http\Controllers\Finance\FinanceStatementController;
use App\Enums\User\UserRole;

// ADMIN
use App\Http\Controllers\Admin\AnnouncementController;
use App\Http\Controllers\Admin\ReminderController;
use App\Http\Controllers\Admin\BlastController;
use App\Http\Controllers\Admin\BlastRecipientController;
use App\Http\Controllers\Admin\BlastMessageTemplateController;
use App\Http\Controllers\Admin\BlastTunggakanController;
use App\Http\Controllers\Admin\ThemeController;

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

    if (Auth::user()->role === UserRole::SYSTEM_MANAGEMENT->value) {
        return redirect()->route('system-management.index');
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

Route::prefix('system-management')
    ->name('system-management.')
    ->controller(SystemManagementController::class)
    ->group(function () {
        Route::middleware('guest')->group(function () {
            Route::get('/login', 'login')->name('login');
            Route::post('/login', 'authenticate')->middleware('throttle:system-management-login')->name('login.submit');
        });

        Route::middleware(['auth', 'system_management'])->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/status', 'status')->name('status');
            Route::get('/maintenance', 'maintenance')->name('maintenance');
            Route::get('/blast-flow', 'blastFlow')->name('blast-flow');
            Route::get('/audit', 'audit')->name('audit');
            Route::get('/users', 'users')->name('users');
            Route::get('/permissions', 'permissions')->name('permissions');
            Route::get('/ai', 'ai')->name('ai');
            Route::get('/api-tester', 'apiTester')->name('api-tester');
            Route::get('/cms', 'cms')->name('cms');
            Route::get('/features', 'features')->name('features');
            Route::get('/feature-access', 'featureAccess')->name('feature-access');
            Route::get('/archives', 'archives')->name('archives');
            Route::post('/users/{user}/password', 'resetUserPassword')->name('users.password');
            Route::post('/permissions', 'updatePermission')->name('permissions.update');
            Route::post('/features', 'storeFeature')->name('features.store');
            Route::patch('/features/{featureFlag}', 'toggleFeature')->name('features.toggle');
            Route::post('/feature-access', 'updateFeatureAccess')->name('feature-access.update');
            Route::post('/maintenance', 'updateMaintenance')->name('maintenance.update');
            Route::post('/api-tester/send', 'sendApiRequest')->name('api-tester.send');
            Route::post('/cms', 'updateCms')->name('cms.update');
            Route::post('/ai/feature-draft', 'draftFeatureWithAi')->name('ai.feature-draft');
            Route::post('/ai/execute', 'executeAiAction')->name('ai.execute');
        });
    });

Route::post('/locale/{locale}', [LocaleController::class, 'update'])
    ->name('locale.update');

Route::get('/announcement/track/{token}', [AnnouncementTrackingController::class, 'open'])
    ->name('announcement.track.open');

Route::middleware('auth')->post('/logout', function () {
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

Route::prefix('dashboard/maintenance-notification-recipients')
    ->name('dashboard.maintenance-notification-recipients.')
    ->middleware(['auth', 'role:' . UserRole::IT_SUPPORT->value . ',' . UserRole::SYSTEM_MANAGEMENT->value])
    ->controller(MaintenanceNotificationRecipientController::class)
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::delete('/{recipient}', 'destroy')->name('destroy');
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
        Route::get('/ac', 'ac')->name('ac.index');
        Route::get('/bangunan-sarana-prasarana', 'buildingInfrastructure')->name('building-infrastructure.index');
        Route::get('/elektronik', 'electronic')->name('electronic.index');
        Route::get('/inventaris-ruangan', 'roomInventory')->name('room-inventory.index');
        Route::get('/kendaraan', 'vehicle')->name('vehicle.index');
        Route::get('/komputer', 'computer')->name('computer.index');
        Route::get('/register', 'showRegisterForm')
            ->middleware('check_access:asset_management.write')
            ->name('register-form');
        Route::get('/edit/{id}', 'showEditForm')
            ->middleware('check_access:asset_management.update')
            ->name('edit-form');
        Route::get('/{id}/qr-code', 'showQrCode')->name('qr-code');
        Route::get('/{id}/qr-code/pdf', 'downloadQrCodePdf')->name('qr-code.pdf');
        Route::get('/download-qr-code', 'downloadQrCode')->name('download-qr-code');
        Route::get('/templates/{category}/download', 'downloadTemplate')->name('download-template');
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
    ->middleware('redirect_legacy_asset_host')
    ->controller(MaintenanceReportController::class)
    ->group(function () {
        Route::middleware(['auth', 'check_access:maintenance_report.read'])->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/{id}', 'show')->name('detail');
            Route::get('/export/excel', 'exportExcel')
                ->name('export-excel');
            Route::get('/export/pdf', 'exportPdf')
                ->name('export-pdf');
            Route::put('/', 'update')
                ->middleware('check_access:maintenance_report.update')
                ->name('update');
            Route::put('/update/status', 'updateStatus')
                ->middleware('check_access:maintenance_report.update_status')
                ->name('update-status');
            Route::post('/{id}/notify', 'sendNotification')
                ->middleware('check_access:maintenance_report.update')
                ->name('notify');
            Route::delete('/{id}', 'delete')
                ->middleware('check_access:maintenance_report.delete')
                ->name('delete');
        });

        Route::post('/submit', 'store')
            ->middleware('throttle:public-maintenance-submission')
            ->name('submit');
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
        Route::get('/login-history', 'loginHistory')->name('login-history');
        Route::get('/{id}', 'show')->name('show');
        Route::post('/', 'store')
            ->middleware('check_access:user_management.write')
            ->name('store');
        Route::post('/reset-password/{id}', 'sendResetPasswordLink')
            ->middleware('check_access:user_management.update')
            ->name('send-reset-password-link');
        Route::post('/{id}/password', 'updatePassword')
            ->middleware(['role:' . UserRole::IT_SUPPORT->value . ',' . UserRole::SYSTEM_MANAGEMENT->value, 'check_access:user_management.update'])
            ->name('password.update');
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
| Note for maintainers:
| - /finance/depreciation is the current manual calculator and log UI.
| - automated asset-based depreciation for period closing is documented in
|   docs/finance-asset-depreciation.md and is not fully wired yet.
*/
Route::prefix('finance')
    ->name('finance.')
    ->middleware(['auth', 'ensure_finance_access'])
    ->group(function () {
        Route::get('/dashboard', [FinanceDashboardController::class, 'index'])
            ->middleware('check_access:finance_report.read')
            ->name('dashboard');

        Route::patch('/categories/{category}/visibility', [FinanceCategoryController::class, 'visibility'])
            ->middleware('role:' . UserRole::IT_SUPPORT->value . ',' . UserRole::SYSTEM_MANAGEMENT->value)
            ->name('categories.visibility');

        Route::resource('categories', FinanceCategoryController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->middleware('role:' . UserRole::IT_SUPPORT->value . ',' . UserRole::SYSTEM_MANAGEMENT->value);

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

        Route::get('/report/balance-sheet/manage', [FinanceStatementController::class, 'manageBalanceSheet'])
            ->middleware('check_access:finance_balance_sheet.read')
            ->name('report.balance-sheet.manage');

        Route::get('/report/balance-sheet/download', [FinanceStatementController::class, 'downloadBalanceSheet'])
            ->middleware('check_access:finance_balance_sheet.read')
            ->name('report.balance-sheet.download');

        Route::get('/report/balance-sheet/template/download', [FinanceStatementController::class, 'downloadBalanceSheetTemplate'])
            ->middleware('check_access:finance_balance_sheet.read')
            ->name('report.balance-sheet.template.download');

        Route::post('/report/balance-sheet/import', [FinanceStatementController::class, 'importBalanceSheetExcel'])
            ->middleware('check_access:finance_report.generate')
            ->name('report.balance-sheet.import');

        Route::post('/report/balance-sheet/rows', [FinanceStatementController::class, 'storeBalanceSheetRow'])
            ->middleware('check_access:finance_report.generate')
            ->name('report.balance-sheet.rows.store');

        Route::put('/report/balance-sheet/rows/{row}', [FinanceStatementController::class, 'updateBalanceSheetRow'])
            ->middleware('check_access:finance_report.generate')
            ->name('report.balance-sheet.rows.update');

        Route::patch('/report/balance-sheet/rows/category', [FinanceStatementController::class, 'bulkUpdateBalanceSheetRowsCategory'])
            ->middleware('check_access:finance_report.generate')
            ->name('report.balance-sheet.rows.category');

        Route::delete('/report/balance-sheet/rows/{row}', [FinanceStatementController::class, 'destroyBalanceSheetRow'])
            ->middleware('check_access:finance_report.generate')
            ->name('report.balance-sheet.rows.destroy');

        Route::get('/report/profit-loss', [FinanceStatementController::class, 'profitLoss'])
            ->middleware('check_access:finance_profit_loss.read')
            ->name('report.profit-loss');

        Route::get('/report/profit-loss/manage', [FinanceStatementController::class, 'manageProfitLoss'])
            ->middleware('check_access:finance_profit_loss.read')
            ->name('report.profit-loss.manage');

        Route::get('/report/profit-loss/download', [FinanceStatementController::class, 'downloadProfitLoss'])
            ->middleware('check_access:finance_profit_loss.read')
            ->name('report.profit-loss.download');

        Route::get('/report/profit-loss/template/download', [FinanceStatementController::class, 'downloadProfitLossTemplate'])
            ->middleware('check_access:finance_profit_loss.read')
            ->name('report.profit-loss.template.download');

        Route::post('/report/profit-loss/import', [FinanceStatementController::class, 'importProfitLossExcel'])
            ->middleware('check_access:finance_report.generate')
            ->name('report.profit-loss.import');

        Route::post('/report/profit-loss/rows', [FinanceStatementController::class, 'storeProfitLossRow'])
            ->middleware('check_access:finance_report.generate')
            ->name('report.profit-loss.rows.store');

        Route::put('/report/profit-loss/rows/{row}', [FinanceStatementController::class, 'updateProfitLossRow'])
            ->middleware('check_access:finance_report.generate')
            ->name('report.profit-loss.rows.update');

        Route::patch('/report/profit-loss/rows/category', [FinanceStatementController::class, 'bulkUpdateProfitLossRowsCategory'])
            ->middleware('check_access:finance_report.generate')
            ->name('report.profit-loss.rows.category');

        Route::delete('/report/profit-loss/rows/{row}', [FinanceStatementController::class, 'destroyProfitLossRow'])
            ->middleware('check_access:finance_report.generate')
            ->name('report.profit-loss.rows.destroy');

        Route::get('/report/general-ledger', [FinanceStatementController::class, 'generalLedger'])
            ->middleware('check_access:finance_general_ledger.read')
            ->name('report.general-ledger');

        Route::get('/report/general-ledger/manage', [FinanceStatementController::class, 'manageGeneralLedger'])
            ->middleware('check_access:finance_general_ledger.read')
            ->name('report.general-ledger.manage');

        Route::get('/report/general-ledger/download', [FinanceStatementController::class, 'downloadGeneralLedger'])
            ->middleware('check_access:finance_general_ledger.read')
            ->name('report.general-ledger.download');

        Route::get('/report/general-ledger/template/download', [FinanceStatementController::class, 'downloadGeneralLedgerTemplate'])
            ->middleware('check_access:finance_general_ledger.read')
            ->name('report.general-ledger.template.download');

        Route::post('/report/general-ledger/import', [FinanceStatementController::class, 'importGeneralLedgerExcel'])
            ->middleware('check_access:finance_report.generate')
            ->name('report.general-ledger.import');

        Route::post('/report/general-ledger/entries', [FinanceStatementController::class, 'storeGeneralLedgerEntry'])
            ->middleware('check_access:finance_report.generate')
            ->name('report.general-ledger.entries.store');

        Route::put('/report/general-ledger/entries/{entry}', [FinanceStatementController::class, 'updateGeneralLedgerEntry'])
            ->middleware('check_access:finance_report.generate')
            ->name('report.general-ledger.entries.update');

        Route::patch('/report/general-ledger/entries/category', [FinanceStatementController::class, 'bulkUpdateGeneralLedgerEntriesCategory'])
            ->middleware('check_access:finance_report.generate')
            ->name('report.general-ledger.entries.category');

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

        Route::get('/report/snapshots/download', [FinanceReportController::class, 'downloadSnapshots'])
            ->middleware('check_access:finance_report.read')
            ->name('report.snapshots.download');

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

                Route::get('/download-posted', [FinanceInvoiceController::class, 'downloadPosted'])
                    ->middleware('check_access:finance_invoice.read')
                    ->name('download-posted');

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
| Legacy Tunggakan Routes
|--------------------------------------------------------------------------
| Tunggakan now belongs to the Blasting module. These route names keep older
| cached menus, compiled views, and bookmarks from failing while pointing users
| and post-action redirects back to the new module location.
*/
Route::prefix('finance/tunggakan')
    ->name('finance.tunggakan.')
    ->middleware(['auth', 'check_access:admin_blast.read'])
    ->group(function () {
        Route::get('/', fn () => redirect()->route('admin.blast.tunggakan.index'))
            ->name('index');

        Route::get('/version', [BlastTunggakanController::class, 'version'])
            ->name('version');

        Route::post('/manual', [BlastTunggakanController::class, 'storeManual'])
            ->middleware('check_access:admin_blast.send')
            ->name('manual.store');

        Route::post('/import', [BlastTunggakanController::class, 'importExcel'])
            ->middleware('check_access:admin_blast.send')
            ->name('import');

        Route::post('/sync-db', [BlastTunggakanController::class, 'syncDatabase'])
            ->middleware('check_access:admin_blast.send')
            ->name('sync-db');

        Route::post('/template-default', [BlastTunggakanController::class, 'createDefaultTemplate'])
            ->middleware('check_access:admin_blast.send')
            ->name('template-default');

        Route::post('/blast-whatsapp', [BlastTunggakanController::class, 'blastWhatsapp'])
            ->middleware('check_access:admin_blast.send')
            ->name('blast-whatsapp');

        Route::delete('/delete-all', [BlastTunggakanController::class, 'destroyAll'])
            ->middleware('check_access:admin_blast.send')
            ->name('destroy-all');

        Route::put('/{record}', [BlastTunggakanController::class, 'update'])
            ->whereUuid('record')
            ->middleware('check_access:admin_blast.send')
            ->name('update');

        Route::delete('/{record}', [BlastTunggakanController::class, 'destroy'])
            ->whereUuid('record')
            ->middleware('check_access:admin_blast.send')
            ->name('destroy');
    });

/*
|--------------------------------------------------------------------------
| Public
|--------------------------------------------------------------------------
*/
Route::get('assets/{id}', [PublicAssetController::class, 'show'])
    ->middleware('redirect_legacy_asset_host')
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

        Route::prefix('theme')
            ->name('theme.')
            ->middleware('role:' . UserRole::IT_SUPPORT->value . ',' . UserRole::SYSTEM_MANAGEMENT->value)
            ->controller(ThemeController::class)
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::put('/', 'update')->name('update');
                Route::post('/image', 'image')->name('image');
                Route::delete('/reset', 'reset')->name('reset');
            });

        /* ================= ANNOUNCEMENTS ================= */
        Route::prefix('announcements')
            ->middleware('check_access:admin_announcement.read')
            ->group(function () {
                Route::get('/', [AnnouncementController::class, 'index'])
                    ->name('announcements.index');
                Route::get('/stats', [AnnouncementController::class, 'stats'])
                    ->name('announcements.stats');
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
                Route::get('/whatsapp/gateway-queue', [BlastController::class, 'whatsappGatewayQueueStatus'])
                    ->name('whatsapp.gateway-queue');
                Route::post('/whatsapp/gateway-queue/clear', [BlastController::class, 'whatsappGatewayQueueClear'])
                    ->name('whatsapp.gateway-queue.clear');
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

                Route::prefix('tunggakan')
                    ->name('tunggakan.')
                    ->group(function () {
                        Route::get('/', [BlastTunggakanController::class, 'index'])
                            ->name('index');

                        Route::get('/version', [BlastTunggakanController::class, 'version'])
                            ->name('version');

                        Route::post('/manual', [BlastTunggakanController::class, 'storeManual'])
                            ->middleware('check_access:admin_blast.send')
                            ->name('manual.store');

                        Route::post('/import', [BlastTunggakanController::class, 'importExcel'])
                            ->middleware('check_access:admin_blast.send')
                            ->name('import');

                        Route::post('/sync-db', [BlastTunggakanController::class, 'syncDatabase'])
                            ->middleware('check_access:admin_blast.send')
                            ->name('sync-db');

                        Route::post('/template-default', [BlastTunggakanController::class, 'createDefaultTemplate'])
                            ->middleware('check_access:admin_blast.send')
                            ->name('template-default');

                        Route::post('/blast-whatsapp', [BlastTunggakanController::class, 'blastWhatsapp'])
                            ->middleware('check_access:admin_blast.send')
                            ->name('blast-whatsapp');

                        Route::delete('/delete-all', [BlastTunggakanController::class, 'destroyAll'])
                            ->middleware('check_access:admin_blast.send')
                            ->name('destroy-all');

                        Route::put('/{record}', [BlastTunggakanController::class, 'update'])
                            ->whereUuid('record')
                            ->middleware('check_access:admin_blast.send')
                            ->name('update');

                        Route::delete('/{record}', [BlastTunggakanController::class, 'destroy'])
                            ->whereUuid('record')
                            ->middleware('check_access:admin_blast.send')
                            ->name('destroy');
                    });

                // Email
                Route::get('/email', [BlastController::class, 'email'])->name('email');
                Route::get('/email/accounts', [BlastController::class, 'emailAccounts'])
                    ->name('email.accounts');
                Route::post('/email/accounts', [BlastController::class, 'storeEmailAccount'])
                    ->middleware('check_access:admin_blast.send')
                    ->name('email.accounts.store');
                Route::put('/email/accounts/{emailAccount}', [BlastController::class, 'updateEmailAccount'])
                    ->middleware('check_access:admin_blast.send')
                    ->name('email.accounts.update');
                Route::post('/email/accounts/{emailAccount}/activate', [BlastController::class, 'activateEmailAccount'])
                    ->middleware('check_access:admin_blast.send')
                    ->name('email.accounts.activate');
                Route::patch('/email/accounts/{emailAccount}/enabled', [BlastController::class, 'toggleEmailAccountEnabled'])
                    ->middleware('check_access:admin_blast.send')
                    ->name('email.accounts.enabled');
                Route::post('/email/accounts/{emailAccount}/test', [BlastController::class, 'testEmailAccount'])
                    ->middleware('check_access:admin_blast.send')
                    ->name('email.accounts.test');
                Route::delete('/email/accounts/{emailAccount}', [BlastController::class, 'destroyEmailAccount'])
                    ->middleware('check_access:admin_blast.send')
                    ->name('email.accounts.destroy');
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
                    Route::get('/templates/{template}/download', [BlastRecipientController::class, 'downloadTemplate'])
                        ->name('templates.download')
                        ->where('template', 'siswa|karyawan|umum');
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
                    Route::get('/general', [BlastRecipientController::class, 'generalIndex'])
                        ->name('general.index');
                    Route::get('/general/create', [BlastRecipientController::class, 'generalCreate'])
                        ->middleware('check_access:blast_recipient.create')
                        ->name('general.create');
                    Route::post('/general', [BlastRecipientController::class, 'generalStore'])
                        ->middleware('check_access:blast_recipient.create')
                        ->name('general.store');
                    Route::get('/general/{id}/edit', [BlastRecipientController::class, 'generalEdit'])
                        ->middleware('check_access:blast_recipient.update')
                        ->name('general.edit');
                    Route::put('/general/{id}', [BlastRecipientController::class, 'generalUpdate'])
                        ->middleware('check_access:blast_recipient.update')
                        ->name('general.update');
                    Route::post('/general/import', [BlastRecipientController::class, 'importGeneral'])
                        ->middleware('check_access:blast_recipient.import')
                        ->name('general.import');
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
                    Route::get('/export', [BlastRecipientController::class, 'exportStudents'])
                        ->name('export');
                    Route::post('/bulk-group', [BlastRecipientController::class, 'bulkMoveStudents'])
                        ->middleware('check_access:blast_recipient.update')
                        ->name('bulk-group');
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
                    Route::delete('/general/delete-all', [BlastRecipientController::class, 'destroyAllGeneral'])
                        ->middleware('check_access:blast_recipient.delete')
                        ->name('general.destroy-all');
                    Route::delete('/general/bulk-delete', [BlastRecipientController::class, 'destroySelectedGeneral'])
                        ->middleware('check_access:blast_recipient.delete')
                        ->name('general.bulk-delete');
                    Route::delete('/general/{id}', [BlastRecipientController::class, 'destroyGeneral'])
                        ->middleware('check_access:blast_recipient.delete')
                        ->name('general.destroy');
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
                        Route::post('/{id}/toggle', [BlastMessageTemplateController::class, 'toggle'])
                            ->middleware('check_access:blast_template.update')
                            ->name('templates.toggle');
                        Route::delete('/{id}', [BlastMessageTemplateController::class, 'destroy'])
                            ->middleware('check_access:blast_template.delete')
                            ->name('templates.destroy');
                    });
            });
    });
