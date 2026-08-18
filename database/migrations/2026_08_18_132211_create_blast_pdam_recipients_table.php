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
        Schema::create('blast_pdam_recipients', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('timestamp_excel')->nullable();
            $table->string('nama_lengkap');
            $table->string('instansi_pekerjaan')->nullable();
            $table->string('nomor_telpon');
            $table->string('email')->nullable();
            $table->string('sertifikat')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blast_pdam_recipients');
    }
};
