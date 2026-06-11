<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    private const TABLES = [
        'finance_accounts',
        'finance_invoices',
        'finance_statement_batches',
        'finance_statement_rows',
        'finance_general_ledger_batches',
        'finance_general_ledger_entries',
        'finance_asset_policies',
        'finance_depreciation_runs',
        'finance_depreciation_histories',
        'finance_depreciation_calculation_logs',
        'finance_reconciliation_snapshots',
        'finance_report_snapshots',
    ];

    public function up(): void
    {
        if (!Schema::hasTable('finance_categories')) {
            Schema::create('finance_categories', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->string('name', 120)->unique();
                $table->text('description')->nullable();
                $table->string('status', 20)->default('active');
                $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('created_at')->useCurrent();
                $table->timestamp('updated_at')->nullable();

                $table->index(['status', 'name']);
            });
        }

        $now = now();
        foreach (['TK', 'SD', 'SMP', 'SMK', 'SMA', 'Yayasan'] as $name) {
            $existingCategory = DB::table('finance_categories')
                ->where('name', $name)
                ->first();

            if ($existingCategory === null) {
                DB::table('finance_categories')->insert([
                    'id' => (string) Str::uuid(),
                    'name' => $name,
                    'description' => 'Kategori finance ' . $name,
                    'status' => 'active',
                    'created_by' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                continue;
            }

            DB::table('finance_categories')
                ->where('id', $existingCategory->id)
                ->update([
                    'description' => $existingCategory->description ?? ('Kategori finance ' . $name),
                    'status' => $existingCategory->status ?: 'active',
                    'updated_at' => $now,
                ]);
        }

        foreach (self::TABLES as $tableName) {
            if (!Schema::hasTable($tableName) || Schema::hasColumn($tableName, 'category_id')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table): void {
                $table->foreignUuid('category_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('finance_categories')
                    ->nullOnDelete();

                $table->index('category_id');
            });
        }
    }

    public function down(): void
    {
        foreach (array_reverse(self::TABLES) as $tableName) {
            if (!Schema::hasTable($tableName) || !Schema::hasColumn($tableName, 'category_id')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table): void {
                $table->dropConstrainedForeignId('category_id');
            });
        }

        Schema::dropIfExists('finance_categories');
    }
};
