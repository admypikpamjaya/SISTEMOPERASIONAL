<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feature_flags', function (Blueprint $table) {
            $table->id();
            $table->string('key', 120)->unique();
            $table->string('name', 160);
            $table->text('description')->nullable();
            $table->boolean('is_enabled')->default(false);
            $table->text('rollout_notes')->nullable();
            $table->longText('ai_prompt')->nullable();
            $table->longText('ai_response')->nullable();
            $table->string('status', 40)->default('draft');
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
            $table->foreign('updated_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
            $table->index(['is_enabled', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feature_flags');
    }
};
