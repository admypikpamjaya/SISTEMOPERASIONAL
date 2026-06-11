<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('room_inventory_details', function (Blueprint $table) {
            $table->foreignUuid('asset_id')->primary()->constrained('assets')->cascadeOnDelete();
            $table->string('asset_code')->nullable()->index();
            $table->string('item_type')->nullable();
            $table->string('item_name')->nullable();
            $table->string('material')->nullable();
            $table->string('size')->nullable();
            $table->string('quantity')->nullable();
            $table->date('acquisition_date')->nullable();
            $table->decimal('unit_price', 18, 2)->nullable();
            $table->string('asset_account_code')->nullable();
            $table->unsignedInteger('useful_life_years')->nullable();
            $table->decimal('accumulated_depreciation', 18, 2)->nullable();
            $table->decimal('book_value', 18, 2)->nullable();
            $table->string('condition')->nullable();
            $table->string('status')->nullable();
            $table->text('notes')->nullable();
            $table->string('source_data')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_inventory_details');
    }
};
