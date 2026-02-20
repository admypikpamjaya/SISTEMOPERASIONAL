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
            $table->string('voice_note_path')->nullable()->after('attachment_size');
            $table->string('voice_note_name')->nullable()->after('voice_note_path');
            $table->unsignedBigInteger('voice_note_size')->nullable()->after('voice_note_name');
            $table->timestamp('pinned_at')->nullable()->after('voice_note_size');
            $table->foreignUuid('pinned_by')->nullable()->after('pinned_at')->constrained('users')->nullOnDelete();
            $table->index('pinned_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('discussion_messages', function (Blueprint $table) {
            $table->dropForeign(['pinned_by']);
            $table->dropIndex(['pinned_at']);
            $table->dropColumn([
                'voice_note_path',
                'voice_note_name',
                'voice_note_size',
                'pinned_at',
                'pinned_by',
            ]);
        });
    }
};
