<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('finance_depreciation_calculation_logs', function (Blueprint $table) {
            $table->date('period_start_date')->nullable()->after('asset_id');
            $table->date('period_end_date')->nullable()->after('period_start_date');
        });
    }

    public function down(): void
    {
        Schema::table('finance_depreciation_calculation_logs', function (Blueprint $table) {
            $table->dropColumn([
                'period_start_date',
                'period_end_date',
            ]);
        });
    }
};
