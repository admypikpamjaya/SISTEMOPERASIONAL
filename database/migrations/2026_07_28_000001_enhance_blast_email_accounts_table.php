<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('blast_email_accounts')) {
            return;
        }

        Schema::table('blast_email_accounts', function (Blueprint $table): void {
            if (!Schema::hasColumn('blast_email_accounts', 'provider')) {
                $table->string('provider', 32)->default('gmail')->index();
            }

            if (!Schema::hasColumn('blast_email_accounts', 'reply_to_address')) {
                $table->string('reply_to_address')->nullable();
            }

            if (!Schema::hasColumn('blast_email_accounts', 'smtp_timeout')) {
                $table->unsignedSmallInteger('smtp_timeout')->default(30);
            }

            if (!Schema::hasColumn('blast_email_accounts', 'daily_limit')) {
                $table->unsignedInteger('daily_limit')->nullable();
            }

            if (!Schema::hasColumn('blast_email_accounts', 'daily_sent_count')) {
                $table->unsignedInteger('daily_sent_count')->default(0);
            }

            if (!Schema::hasColumn('blast_email_accounts', 'daily_failed_count')) {
                $table->unsignedInteger('daily_failed_count')->default(0);
            }

            if (!Schema::hasColumn('blast_email_accounts', 'quota_reset_date')) {
                $table->date('quota_reset_date')->nullable();
            }

            if (!Schema::hasColumn('blast_email_accounts', 'last_used_at')) {
                $table->timestamp('last_used_at')->nullable();
            }

            if (!Schema::hasColumn('blast_email_accounts', 'last_send_status')) {
                $table->string('last_send_status', 32)->nullable();
            }

            if (!Schema::hasColumn('blast_email_accounts', 'last_send_message')) {
                $table->text('last_send_message')->nullable();
            }

            if (!Schema::hasColumn('blast_email_accounts', 'last_error_at')) {
                $table->timestamp('last_error_at')->nullable();
            }

            if (!Schema::hasColumn('blast_email_accounts', 'metadata')) {
                $table->json('metadata')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('blast_email_accounts')) {
            return;
        }

        Schema::table('blast_email_accounts', function (Blueprint $table): void {
            $columns = [
                'provider',
                'reply_to_address',
                'smtp_timeout',
                'daily_limit',
                'daily_sent_count',
                'daily_failed_count',
                'quota_reset_date',
                'last_used_at',
                'last_send_status',
                'last_send_message',
                'last_error_at',
                'metadata',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('blast_email_accounts', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
