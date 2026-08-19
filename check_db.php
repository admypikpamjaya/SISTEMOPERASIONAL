<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$rows = App\Models\FinanceStatementRow::count();
$bsFinanceType = App\Models\FinanceStatementRow::where('finance_type', 'BALANCE_SHEET')->count();
$bsBatch = App\Models\FinanceStatementRow::whereHas('batch', function($q) { $q->where('statement_type', 'BALANCE_SHEET'); })->count();

$batches = App\Models\FinanceStatementBatch::count();
$bsBatches = App\Models\FinanceStatementBatch::where('statement_type', 'BALANCE_SHEET')->count();

echo "Rows: $rows\n";
echo "BS Finance Type Rows: $bsFinanceType\n";
echo "BS Batch Rows: $bsBatch\n";
echo "Batches: $batches\n";
echo "BS Batches: $bsBatches\n";

// Profit Loss
$plFinanceType = App\Models\FinanceStatementRow::where('finance_type', 'PROFIT_LOSS')->count();
$plBatch = App\Models\FinanceStatementRow::whereHas('batch', function($q) { $q->where('statement_type', 'PROFIT_LOSS'); })->count();
$plBatches = App\Models\FinanceStatementBatch::where('statement_type', 'PROFIT_LOSS')->count();

echo "PL Finance Type Rows: $plFinanceType\n";
echo "PL Batch Rows: $plBatch\n";
echo "PL Batches: $plBatches\n";

// General Ledger
$glEntries = App\Models\FinanceGeneralLedgerEntry::count();
echo "GL Entries: $glEntries\n";

