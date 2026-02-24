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
        Schema::create('finance_account_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('finance_account_id')
                ->nullable()
                ->constrained('finance_accounts')
                ->nullOnDelete();
            $table->string('action', 32);
            $table->json('before_data')->nullable();
            $table->json('after_data')->nullable();
            $table->foreignUuid('actor_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamps();

            $table->index('finance_account_id', 'finance_account_logs_account_idx');
            $table->index('created_at', 'finance_account_logs_created_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('finance_account_logs');
    }
};
