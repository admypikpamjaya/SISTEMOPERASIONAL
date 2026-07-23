<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_permission_overrides', function (Blueprint $table) {
            $table->id();
            $table->string('role', 80);
            $table->string('permission', 120);
            $table->boolean('allowed')->default(true);
            $table->uuid('updated_by')->nullable();
            $table->timestamps();

            $table->unique(['role', 'permission']);
            $table->foreign('updated_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_permission_overrides');
    }
};
