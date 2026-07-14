<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('blast_general_recipients')) {
            return;
        }

        Schema::table('blast_general_recipients', function (Blueprint $table): void {
            if (!Schema::hasColumn('blast_general_recipients', 'instansi')) {
                $table->string('instansi')->nullable()->after('whatsapp');
            }

            if (!Schema::hasColumn('blast_general_recipients', 'email')) {
                $table->string('email')->nullable()->after('instansi');
            }

            if (!Schema::hasColumn('blast_general_recipients', 'sertifikat')) {
                $table->text('sertifikat')->nullable()->after('email');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('blast_general_recipients')) {
            return;
        }

        Schema::table('blast_general_recipients', function (Blueprint $table): void {
            foreach (['sertifikat', 'email', 'instansi'] as $column) {
                if (Schema::hasColumn('blast_general_recipients', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
