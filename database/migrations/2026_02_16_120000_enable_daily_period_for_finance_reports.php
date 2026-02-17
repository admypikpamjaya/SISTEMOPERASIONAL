<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('finance_periods', 'day')) {
            Schema::table('finance_periods', function (Blueprint $table) {
                $table->unsignedTinyInteger('day')->default(0)->after('month');
            });
        }

        DB::statement("UPDATE finance_periods SET day = 0 WHERE day IS NULL");

        $this->dropIndexIfExists('finance_periods', 'finance_periods_unique_period');
        $this->dropIndexIfExists('finance_periods', 'finance_periods_idx_year_month_day');

        DB::statement("ALTER TABLE finance_periods MODIFY period_type ENUM('DAILY','MONTHLY','YEARLY') NOT NULL");

        Schema::table('finance_periods', function (Blueprint $table) {
            $table->unique(['period_type', 'year', 'month', 'day'], 'finance_periods_unique_period');
            $table->index(['year', 'month', 'day'], 'finance_periods_idx_year_month_day');
        });

        DB::statement("ALTER TABLE finance_report_snapshots MODIFY report_type ENUM('DAILY','MONTHLY','YEARLY') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("UPDATE finance_periods SET period_type = 'MONTHLY', day = 0 WHERE period_type = 'DAILY'");
        DB::statement("UPDATE finance_report_snapshots SET report_type = 'MONTHLY' WHERE report_type = 'DAILY'");

        $this->dropIndexIfExists('finance_periods', 'finance_periods_unique_period');
        $this->dropIndexIfExists('finance_periods', 'finance_periods_idx_year_month_day');

        DB::statement("ALTER TABLE finance_periods MODIFY period_type ENUM('MONTHLY','YEARLY') NOT NULL");
        DB::statement("ALTER TABLE finance_report_snapshots MODIFY report_type ENUM('MONTHLY','YEARLY') NOT NULL");

        if (Schema::hasColumn('finance_periods', 'day')) {
            Schema::table('finance_periods', function (Blueprint $table) {
                $table->dropColumn('day');
            });
        }

        Schema::table('finance_periods', function (Blueprint $table) {
            $table->unique(['period_type', 'year', 'month'], 'finance_periods_unique_period');
        });
    }

    private function dropIndexIfExists(string $table, string $index): void
    {
        try {
            DB::statement("ALTER TABLE {$table} DROP INDEX {$index}");
        } catch (\Throwable $e) {
            // index may not exist in current schema, safe to ignore
        }
    }
};
