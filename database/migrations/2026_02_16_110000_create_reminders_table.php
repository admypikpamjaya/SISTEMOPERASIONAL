<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('remind_at');
            $table->unsignedInteger('alert_before_minutes')->default(30);
            $table->enum('type', ['GENERAL', 'ANNOUNCEMENT'])->default('GENERAL');
            $table->foreignId('announcement_id')->nullable()->constrained('announcements')->nullOnDelete();
            $table->boolean('is_active')->default(true);

            $table->uuid('created_by');
            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();

            $table->timestamp('deactivated_at')->nullable();
            $table->uuid('deactivated_by')->nullable();
            $table->foreign('deactivated_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(['is_active', 'remind_at']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reminders');
    }
};
