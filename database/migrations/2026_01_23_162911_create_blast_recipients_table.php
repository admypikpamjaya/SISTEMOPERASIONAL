<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blast_recipients', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Data inti
            $table->string('nama_siswa');
            $table->string('kelas');
            $table->string('nama_wali');

            // Channel tujuan
            $table->string('wa_wali')->nullable();
            $table->string('email_wali')->nullable();

            // Opsional
            $table->text('catatan')->nullable();

            // Status validasi
            $table->boolean('is_valid')->default(false);
            $table->text('validation_error')->nullable();

            $table->timestamps();

            // Indexing ringan
            $table->index('wa_wali');
            $table->index('email_wali');
            $table->index('is_valid');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blast_recipients');
    }
};
