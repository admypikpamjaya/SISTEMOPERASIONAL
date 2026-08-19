<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\Finance\FinanceImportedStatementService;
use App\Services\Finance\FinanceGeneralLedgerService;
use App\Models\FinanceStatementBatch;

$importedService = app(FinanceImportedStatementService::class);
$glService = app(FinanceGeneralLedgerService::class);

$cat = \App\Models\FinanceCategory::query()->first();
$categoryId = $cat ? $cat->id : null;

echo "Testing Balance Sheet...\n";
try {
    $summary1 = $importedService->importFromExcel(
        FinanceStatementBatch::TYPE_BALANCE_SHEET,
        __DIR__ . '/docs/LEMBAR SALDO 2025 FIX.xlsx',
        'LEMBAR SALDO 2025 FIX.xlsx',
        (string)$categoryId,
        'Test Balance Sheet',
        null,
        null
    );
    echo "Success! Imported Balance Sheet with " . $summary1['summary']['account_count'] . " accounts.\n";
} catch (\Exception $e) {
    echo "Error Balance Sheet: " . $e->getMessage() . "\n" . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\nTesting Profit Loss...\n";
try {
    $summary2 = $importedService->importFromExcel(
        FinanceStatementBatch::TYPE_PROFIT_LOSS,
        __DIR__ . '/docs/LABA RUGI 2025 FIX.xlsx',
        'LABA RUGI 2025 FIX.xlsx',
        (string)$categoryId,
        'Test Profit Loss',
        null,
        null
    );
    echo "Success! Imported Profit Loss with " . $summary2['summary']['account_count'] . " accounts.\n";
} catch (\Exception $e) {
    echo "Error Profit Loss: " . $e->getMessage() . "\n" . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\nTesting General Ledger...\n";
try {
    $summary3 = $glService->importFromExcel(
        __DIR__ . '/docs/BUKU BESAR 2025 FIX.xlsx',
        'BUKU BESAR 2025 FIX.xlsx',
        (string)$categoryId,
        'Test General Ledger',
        null,
        null
    );
    echo "Success! Imported General Ledger with " . $summary3['account_count'] . " accounts and " . $summary3['inserted'] . " entries.\n";
} catch (\Exception $e) {
    echo "Error General Ledger: " . $e->getMessage() . "\n" . $e->getFile() . ":" . $e->getLine() . "\n";
}
