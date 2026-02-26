<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blast_recipients', function (Blueprint $table) {
            if (!Schema::hasColumn('blast_recipients', 'wa_wali_2')) {
                $table->string('wa_wali_2')->nullable()->after('wa_wali');
                $table->index('wa_wali_2');
            }
        });
    }

    public function down(): void
    {
        Schema::table('blast_recipients', function (Blueprint $table) {
            if (Schema::hasColumn('blast_recipients', 'wa_wali_2')) {
                $table->dropIndex(['wa_wali_2']);
                $table->dropColumn('wa_wali_2');
            }
        });
    }
};
