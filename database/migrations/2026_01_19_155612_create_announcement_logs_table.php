<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('announcement_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('announcement_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('channel'); // email | whatsapp
            $table->string('target');

            $table->string('status');
            $table->text('response')->nullable();

            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcement_logs');
    }
};
