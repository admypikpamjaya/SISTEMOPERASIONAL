<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('blast_logs', function (Blueprint $table) {
            $table->longText('message_snapshot')->nullable();
            $table->text('error_message')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('blast_logs', function (Blueprint $table) {
            $table->dropColumn([
                'message_snapshot',
                'error_message',
            ]);
        });
    }
};
