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
        Schema::table('discussion_messages', function (Blueprint $table) {
            $table->timestamp('pin_expires_at')->nullable()->after('pinned_at');
            $table->index('pin_expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('discussion_messages', function (Blueprint $table) {
            $table->dropIndex(['pin_expires_at']);
            $table->dropColumn('pin_expires_at');
        });
    }
};
