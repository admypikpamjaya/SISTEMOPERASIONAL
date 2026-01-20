<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blast_messages', function (Blueprint $table) {
            // DROP primary key lama (jika ada)
            $table->dropPrimary();
        });

        DB::statement('ALTER TABLE blast_messages MODIFY id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY');
    }

    public function down(): void
    {
        // tidak perlu rollback untuk phase ini
    }
};
