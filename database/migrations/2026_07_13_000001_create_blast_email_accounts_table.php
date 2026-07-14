<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blast_email_accounts', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('label');
            $table->string('email_address');
            $table->string('from_name')->nullable();
            $table->string('host');
            $table->unsignedSmallInteger('port')->default(587);
            $table->string('encryption', 16)->nullable();
            $table->string('username')->nullable();
            $table->text('password')->nullable();
            $table->boolean('is_active')->default(false);
            $table->boolean('is_enabled')->default(true);
            $table->timestamp('last_tested_at')->nullable();
            $table->string('last_test_status', 32)->nullable();
            $table->text('last_test_message')->nullable();
            $table->timestamps();

            $table->index(['is_enabled', 'is_active'], 'blast_email_accounts_enabled_active_idx');
            $table->index('email_address', 'blast_email_accounts_email_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blast_email_accounts');
    }
};
