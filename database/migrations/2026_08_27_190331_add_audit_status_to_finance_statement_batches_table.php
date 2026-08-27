<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('finance_statement_batches', function (Blueprint $table) {
            $table->enum('audit_status', ['UNAUDITED', 'PRE_AUDIT', 'AUDITED'])
                ->default('UNAUDITED')
                ->after('statement_type');
            $table->index(['audit_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('finance_statement_batches', function (Blueprint $table) {
            $table->dropIndex(['audit_status']);
            $table->dropColumn('audit_status');
        });
    }
};
