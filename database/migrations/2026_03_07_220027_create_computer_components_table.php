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
        Schema::create('computer_components', function (Blueprint $table) {
            $table->foreignUuid('asset_id')->constrained('assets')->cascadeOnDelete();
            $table->enum('component_type', ['Monitor', 'Motherboard', 'Processor', 'RAM', 'Storage', 'GPU', 'Keyboard / Mouse']);
            $table->string('brand')->nullable();
            $table->string('specification')->nullable();
            $table->string('serial_number')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('computer_components');
    }
};
