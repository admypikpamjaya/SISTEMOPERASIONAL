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
            $table->unsignedBigInteger('reply_to_message_id')
                ->nullable()
                ->after('message');

            $table->foreign('reply_to_message_id')
                ->references('id')
                ->on('discussion_messages')
                ->nullOnDelete();

            $table->index(['channel_id', 'reply_to_message_id'], 'discussion_msg_channel_reply_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('discussion_messages', function (Blueprint $table) {
            $table->dropIndex('discussion_msg_channel_reply_index');
            $table->dropForeign(['reply_to_message_id']);
            $table->dropColumn('reply_to_message_id');
        });
    }
};
