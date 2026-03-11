<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blast_employee_ypik_recipients', function (Blueprint $table) {
            $table->string('dataset', 40)->default('pam_jaya')->after('source');
            $table->index('dataset');
        });

        DB::table('blast_employee_ypik_recipients')
            ->whereNull('dataset')
            ->update(['dataset' => 'pam_jaya']);
    }

    public function down(): void
    {
        Schema::table('blast_employee_ypik_recipients', function (Blueprint $table) {
            $table->dropIndex(['dataset']);
            $table->dropColumn('dataset');
        });
    }
};
